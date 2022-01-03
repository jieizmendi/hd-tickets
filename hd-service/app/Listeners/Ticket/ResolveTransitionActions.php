<?php

namespace App\Listeners\Ticket;

class ResolveTransitionActions extends ResolveActions
{
    /**
     * Handle the event.
     *
     * @param  object  $event
     */
    public function handle($event): void
    {
        $this->callActions($event->ticket, $event->transitionData['actions']);
    }
}
