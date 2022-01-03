<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\v1\Ticket\ReadAllTicketRequest;
use App\Http\Requests\API\v1\Ticket\StoreTicketRequest;
use App\Http\Requests\API\v1\Ticket\TransitionTicketRequest;
use App\Http\Requests\API\v1\Ticket\UpdateTicketRequest;
use App\Http\Resources\v1\TicketResource;
use App\Models\Ticket;
use App\Services\TicketService;
use Illuminate\Support\Facades\Auth;

class TicketController extends Controller
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

        $this->authorizeResource(Ticket::class, 'ticket');
    }

    /**
     * Returns the scope name of the filter.
     */
    private function filtersMap(string $key): string
    {
        return [
            'owner' => 'whereOwner',
            'agent' => 'whereHasAgent',
            'status' => 'whereStatus',
            'priority' => 'wherePriority',
            'anyTag' => 'withAnyTag',
            'allTags' => 'withAllTag',
        ][$key];
    }

    /**
     * Display a listing of the resources.
     */
    public function index(ReadAllTicketRequest $request)
    {
        $query = Ticket::searchFromQuery(['subject', 'content', 'owner.name', 'owner.email'])
            ->sortFromQuery(['id', 'subject', 'priority', 'owner_id']);

        if (Auth::user()->isUser()) {
            $query->whereOwner(Auth::id());
        } else {
            foreach ($request->validated() as $filter => $filterData) {
                if ($filterScope = $this->filtersMap($filter)) {
                    call_user_func([$query, $filterScope], $filterData);
                }
            }
        }

        return TicketResource::collection(
            $query->paginate(request('itemsPerPage', 10))
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTicketRequest $request)
    {
        $ticket = $this->ticketService->store($request->validated());

        return (new TicketResource($ticket))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Ticket $ticket)
    {
        return new TicketResource($ticket);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTicketRequest $request, Ticket $ticket)
    {
        $ticket->update($request->validated());

        return new TicketResource($ticket);
    }

    /**
     * Attempt a ticket transition.
     */
    public function transition(TransitionTicketRequest $request, Ticket $ticket)
    {
        $status = $request->validated()['status'];
        $content = $request->validated()['content'];

        $transitionData = $this->ticketService->getTransitionConfig($ticket->status->name, $status);

        $this->authorize('transition', [$ticket, $transitionData]);

        $this->ticketService->transition($ticket, $status, $content);

        return new TicketResource($ticket);
    }
}
