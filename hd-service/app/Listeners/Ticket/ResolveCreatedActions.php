<?php

namespace App\Listeners\Ticket;

class ResolveCreatedActions extends ResolveActions
{
    /**
     * Handle the event.
     *
     * @param  object  $event
     */
    public function handle($event): void
    {
        $this->callActions($event->ticket, config('hd.default.actions', []));
    }
}
