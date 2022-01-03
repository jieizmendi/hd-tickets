<?php

namespace App\Events\Ticket;

use App\Models\Ticket;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class Transitioned
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Transitioned ticket instance.
     *
     * @var \App\Models\Ticket|null
     */
    public $ticket = null;

    /**
     * Transition data.
     *
     * @var array
     */
    public $transitionData = [];

    /**
     * Create a new event instance.
     */
    public function __construct(Ticket $ticket, array $transitionData)
    {
        $this->ticket = $ticket;
        $this->transitionData = $transitionData;
    }
}
