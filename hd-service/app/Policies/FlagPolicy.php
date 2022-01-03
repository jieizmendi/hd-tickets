<?php

namespace App\Policies;

use App\Models\Flag;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class FlagPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can flag ticket.
     */
    public function create(User $user, Ticket $ticket): bool
    {
        if ($user->isUser()) {
            return $user->id == $ticket->owner_id;
        }

        return true;
    }

    /**
     * Determine whether the user can unflag a ticket.
     */
    public function remove(User $user, Flag $flag): bool
    {
        return $flag->user_id == $user->id;
    }
}
