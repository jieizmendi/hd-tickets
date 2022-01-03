<?php

namespace App\Listeners\Ticket;

use App\Actions\ActionInterface;
use App\Exceptions\Ticket\NotFoundActionException;
use App\Models\Ticket;

abstract class ResolveActions
{
    protected function callActions(Ticket $ticket, array $actions): void
    {
        foreach ($actions as $class) {
            if (!class_exists($class)) {
                throw new NotFoundActionException($class);
            }

            $this->callAction($ticket, new $class());

            activity('actions')
                ->byAnonymous()
                ->performedOn($ticket)
                ->log(last(explode('\\', $class)) . " performed.");
        }
    }

    private function callAction(Ticket $ticket, ActionInterface $action)
    {
        return $action->resolve($ticket);
    }
}
