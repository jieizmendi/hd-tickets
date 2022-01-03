<?php

namespace App\Http\Controllers\API\v1\Ticket;

use App\Http\Controllers\Controller;
use App\Http\Resources\v1\TicketResource;
use App\Models\Ticket;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class FlagController extends Controller
{
    public function create(Ticket $ticket, string $flagType)
    {
        $this->authorize('create', [Flag::class, $ticket]);
        $this->validateFlag($flagType);

        if ($ticket->flags()->whereFlag($flagType)->first()) {
            return $this->sendMessageResponse(__('flags.exists', compact('flagType')), 400);
        }

        $ticket->flags()->create([
            'user_id' => Auth::id(),
            'flag' => $flagType,
        ]);

        return new TicketResource($ticket);
    }

    public function remove(Ticket $ticket, string $flagType)
    {
        $this->validateFlag($flagType);

        if (!$flag = $ticket->flags()->whereFlag($flagType)->first()) {
            return $this->sendMessageResponse(__('flags.not-exists', compact('flagType')), 400);
        }

        $this->authorize('remove', $flag);
        $flag->delete();

        return new TicketResource($ticket);
    }

    private function validateFlag(string $flag): void
    {
        Validator::make(compact('flag'), [
            'flag' => 'required|in:' . implode(",", config('hd.flags')),
        ])->validate();
    }
}
