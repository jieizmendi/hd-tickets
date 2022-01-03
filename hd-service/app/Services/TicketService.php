<?php

namespace App\Services;

use App\Events\Ticket\Created as TicketCreatedEvent;
use App\Events\Ticket\Transitioned as TicketTransitionedEvent;
use App\Exceptions\Ticket\InvalidTransitionException;
use App\Models\Status;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TicketService
{
    /**
     * Create a ticket.
     */
    public function store(array $data): Ticket
    {
        $files = Arr::pull($data, 'files') ?? [];

        $ticket = new Ticket($data);
        $ticket->owner_id = Auth::id();
        $ticket->priority = config('hd.default.priority', floor(config('hd.priorities', 10) / 2));
        $status = config('hd.default.status');

        DB::transaction(function () use ($ticket, $files, $status) {
            $ticket->save();

            $ticket->statuses()->create([
                'name' => $status,
                'user_id' => Auth::id(),
            ]);

            $this->attachFiles($ticket, $files);
        });

        TicketCreatedEvent::dispatch($ticket);

        return $ticket;
    }

    /**
     * Attach files to ticket.
     */
    public function attachFiles(Ticket $ticket, array $files): void
    {
        foreach ($files as $file) {
            $path = "";
            $storageName = md5($file['file']->getClientOriginalName() . microtime());

            $privacy = $file['is_public'] ?? true;
            if (Auth::user()->isUser()) {
                $privacy = true;
            }

            $ticket->files()->create([
                'name' => $file['file']->getClientOriginalName(),
                'location' => "{$path}/{$storageName}",
                'user_id' => Auth::id(),
                'is_public' => $privacy,
            ]);

            $file['file']->storeAs($path, $storageName);
        }
    }

    /**
     * Get transition data.
     */
    public function getTransitionConfig(string $from, string $to): array
    {
        $data = config("hd.transitions.{$from}.{$to}");

        if (!is_array($data)) {
            throw new InvalidTransitionException($from, $to);
        }

        return $data;
    }

    /**
     * Go to next status.
     */
    public function transition(Ticket $ticket, string $to, string $content): void
    {
        $from = $ticket->status->name;
        $ticket->statuses()->create([
            'name' => $to,
            'user_id' => Auth::id(),
            'content' => $content,
        ]);

        TicketTransitionedEvent::dispatch(
            $ticket,
            $this->getTransitionConfig($from, $to)
        );
    }
}
