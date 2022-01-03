<?php

namespace App\Actions;

use App\Models\Ticket;

class NotifyOwnerThatActionIsNecessary implements ActionInterface
{
    /**
     * Resolve the action.
     */
    public function resolve(Ticket $ticket): void
    {
        //dd('# Send owner some email.');
    }
}
