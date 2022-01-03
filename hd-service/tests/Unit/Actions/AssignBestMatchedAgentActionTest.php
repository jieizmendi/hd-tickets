<?php

namespace Tests\Unit\Actions;

use App\Actions\AssignBestMatchedAgentAction;
use App\Models\Tag;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssignBestMatchedAgentActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_assign_one_of_the_less_loaded_agents()
    {
        User::factory()
            ->times(5)
            ->role('Agent')
            ->create()
            ->each(function ($agent) {
                Ticket::factory()
                    ->times(rand(1, 5))
                    ->create()
                    ->each(function ($ticket) use ($agent) {
                        $ticket->agents()->attach($agent);
                    });
            });

        $selecteds = User::factory()->times(2)->role('Agent')->create();
        $ticket = Ticket::factory()->create();

        (new AssignBestMatchedAgentAction())->resolve($ticket);

        $this->assertCount(
            1,
            array_intersect(
                $selecteds->pluck('id')->toArray(),
                $ticket->agents()->get()->pluck('id')->toArray()
            )
        );
    }

    public function test_it_assign_one_of_the_best_tags_matched_agents()
    {
        $tags = Tag::factory()->times(10)->create();
        $tag = Tag::factory()->create();

        User::factory()
            ->times(5)
            ->role('Agent')
            ->create()
            ->each(function ($agent) use ($tags) {
                $agent->tag($tags->random(rand(9, 10)));
            });

        $selecteds = User::factory()
            ->times(2)
            ->role('Agent')
            ->create()
            ->each(function ($agent) use ($tags, $tag) {
                $agent->tag($tags);
                $agent->tag($tag);
            });
        $ticket = Ticket::factory()->create();
        $ticket->tag($tags);
        $ticket->tag($tag);

        (new AssignBestMatchedAgentAction())->resolve($ticket);

        $this->assertCount(
            1,
            array_intersect(
                $selecteds->pluck('id')->toArray(),
                $ticket->agents()->get()->pluck('id')->toArray()
            )
        );
    }

    public function test_it_assign_the_best_matched_agents()
    {
        $tags = Tag::factory()->times(2)->create();
        //$action = new AssignBestMatchedAgentAction();

        // All the tags + half load => w = 0.3 + 0.35
        $a = User::factory()->role('Agent')->create();
        $a->tag($tags);
        Ticket::factory()
            ->times(5)
            ->create()
            ->each(function ($ticket) use ($a) {
                $ticket->agents()->attach($a);
            });

        // No tags + no load => w = 0 + 0.7
        $b = User::factory()->role('Agent')->create();

        $ticket = Ticket::factory()->create();
        $ticket->tag($tags);

        (new AssignBestMatchedAgentAction())->resolve($ticket);

        $this->assertContains(
            $b->id,
            $ticket->agents()->get()->pluck('id')->toArray()
        );
    }
}
