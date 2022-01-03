<?php

namespace App\Http\Controllers\API\v1\Ticket;

use App\Exceptions\UserIsNotAgentException;
use App\Http\Controllers\Controller;
use App\Http\Resources\v1\TicketResource;
use App\Models\Ticket;
use App\Models\User;

class AgentController extends Controller
{
    public function attach(Ticket $ticket, User $user)
    {
        $this->authorize('agents', [$ticket, $user]);

        if (!$user->isAgent()) {
            throw new UserIsNotAgentException($user);
        }

        if ($ticket->agents()->find($user->id)) {
            return $this->sendMessageResponse(__('agents.attached', $user->toArray()), 400);
        }

        $ticket->agents()->attach($user);

        activity('agent')
            ->performedOn($ticket)
            ->withProperties(['agent_id' => $user->id])
            ->log("assigned");

        return new TicketResource($ticket);
    }

    public function detach(Ticket $ticket, User $user)
    {
        $this->authorize('agents', [$ticket, $user]);

        if (!$ticket->agents()->find($user->id)) {
            return $this->sendMessageResponse(__('agents.non-attached', $user->toArray()), 400);
        }

        $ticket->agents()->detach($user);

        activity('agent')
            ->performedOn($ticket)
            ->withProperties(['agent_id' => $user->id])
            ->log("unassigned");

        return new TicketResource($ticket);
    }
}
