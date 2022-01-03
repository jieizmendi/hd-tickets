<?php

namespace App\Actions;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class AssignBestMatchedAgentAction implements ActionInterface
{
    /**
     * Angets collection instace.
     *
     * @var \Illuminate\Support\Collection
     */
    protected $agents = null;

    /**
     * Load weight distribution.
     *
     * @var float
     */
    static $loadWeight = 0.7;

    /**
     * Amount of load were an agent it's cosinder overflow.
     *
     * @var int
     */
    static $loadRoof = 10;

    /**
     * Create instance, let's cache the agents.
     */
    public function __construct()
    {
        $this->agents = Cache::remember('agents', 3600, function () {
            return User::with('tags')->agents()->get();
        });
    }

    /**
     * Resolve the action.
     */
    public function resolve(Ticket $ticket): void
    {
        if ($this->agents->count() == 0) {
            return;
        }

        $ids = $this->maxWeightAgents($ticket->tags->pluck('id')->toArray());

        $ticket->agents()->attach($id = Arr::random($ids));

        activity('agent')
            ->byAnonymous()
            ->performedOn($ticket)
            ->withProperties(['agent_id' => $id])
            ->log("assigned");
    }

    /**
     * Count matching tags per agent.
     */
    private function maxWeightAgents(array $tags): array
    {
        return $this->agents->reduce(function ($carry, $agent) use ($tags) {
            $w = 0;

            // Tag weight
            if (count($tags) > 0) {
                $matches = count(
                    array_intersect(
                        $tags,
                        $agent->tags->pluck('id')->toArray()
                    )
                );

                $w += round(((1 - self::$loadWeight) / count($tags)) * $matches, 2);
            }

            // Load weight
            // Here it's assume that default status it's the workable one.
            $count = Ticket::whereHasAgent($agent->id)
                ->whereStatus(config('hd.default.status'))
                ->count();
            $w += round(self::$loadWeight - ((self::$loadWeight / self::$loadRoof) * $count), 2);

            if ($carry['w'] == $w) {
                $carry['ids'][] = $agent->id;
            } else if ($carry['w'] < $w) {
                return ['w' => $w, 'ids' => [$agent->id]];
            }

            return $carry;
        }, ['w' => 0, 'ids' => []])['ids'];
    }
}
