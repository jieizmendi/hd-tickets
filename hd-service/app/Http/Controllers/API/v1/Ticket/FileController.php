<?php

namespace App\Http\Controllers\API\v1\Ticket;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\v1\Ticket\UploadFilesRequest;
use App\Http\Resources\v1\TicketResource;
use App\Models\File;
use App\Models\Ticket;
use App\Services\TicketService;

class FileController extends Controller
{
    /**
     * Get tickets service.
     *
     * @var \App\Services\TicketService|null
     */
    private $ticketService = null;

    /**
     * Create the controller instance.
     */
    public function __construct(TicketService $ticketService)
    {
        $this->ticketService = $ticketService;
    }

    /**
     * Attach files to the ticket.
     */
    public function add(UploadFilesRequest $request, Ticket $ticket)
    {
        $this->authorize('add', [File::class, $ticket]);

        $this->ticketService->attachFiles($ticket, $request->validated()['files']);

        return new TicketResource($ticket);
    }

    /**
     * Detach files to the ticket.
     */
    public function remove(Ticket $ticket, File $file)
    {
        $this->authorize('remove', [$file, $ticket]);

        $file->delete();

        return new TicketResource($ticket);
    }
}
