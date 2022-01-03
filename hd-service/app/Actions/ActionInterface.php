<?php

namespace App\Actions;

use App\Models\Ticket;

interface ActionInterface
{
    public function resolve(Ticket $ticket): void;
}
