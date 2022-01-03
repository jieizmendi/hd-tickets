<?php

namespace App\Policies;

use App\Models\File;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class FilePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can add a file.
     */
    public function add(User $user, Ticket $ticket): bool
    {
        return $user->isAdmin() ||
        $ticket->owner_id == $user->id ||
        $ticket->hasAgent($user);
    }

    /**
     * Determine whether the user can remove a file.
     */
    public function remove(User $user, File $file, Ticket $ticket): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($ticket->hasAgent($user) || $ticket->owner_id == $user->id) {
            return $file->user_id == $user->id;
        }

        return false;
    }
}
