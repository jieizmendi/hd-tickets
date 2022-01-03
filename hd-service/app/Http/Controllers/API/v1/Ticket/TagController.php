<?php

namespace App\Http\Controllers\API\v1\Ticket;

use App\Http\Controllers\Controller;
use App\Http\Resources\v1\TicketResource;
use App\Models\Tag;
use App\Models\Ticket;

class TagController extends Controller
{
    public function add(Ticket $ticket, Tag $tag)
    {
        $this->authorize('tags', $ticket);

        $ticket->tag($tag);

        return new TicketResource($ticket);
    }

    public function remove(Ticket $ticket, Tag $tag)
    {
        $this->authorize('tags', $ticket);

        $ticket->untag($tag);

        return new TicketResource($ticket);
    }
}
