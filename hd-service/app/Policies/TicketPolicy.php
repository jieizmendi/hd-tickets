<?php

namespace App\Policies;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TicketPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Ticket $ticket): bool
    {
        if ($user->isUser()) {
            return $user->id == $ticket->owner_id;
        }

        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Ticket $ticket): bool
    {
        return $user->id == $ticket->owner_id;
    }

    /**
     * Determine whether the user can transition the model.
     */
    public function transition(User $user, Ticket $ticket, array $transitionData): bool
    {
        if ($transitionData['actor'] == 'Owner') {
            return $user->id == $ticket->owner_id;
        }

        return $ticket->hasAgent($user);
    }

    /**
     * Determine whether the user can tag the model.
     */
    public function tags(User $user, Ticket $ticket): bool
    {
        if ($user->isUser()) {
            return $user->id == $ticket->owner_id;
        }

        return true;
    }

    /**
     * Determine whether the user can attach an agent to the model.
     */
    public function agents(User $user, Ticket $ticket, User $agent): bool
    {
        return $user->isAdmin() || ($agent->isAgent() && $user->id == $agent->id);
    }
}
