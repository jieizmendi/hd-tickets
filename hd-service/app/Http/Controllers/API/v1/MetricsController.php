<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\v1\DashboardRequest;
use App\Models\Ticket;
use App\Services\MetricsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MetricsController extends Controller
{
    /**
     * Get metrics service.
     *
     * @var \App\Services\MetricsService|null
     */
    private $ticketService = null;

    /**
     * Create the controller instance.
     */
    public function __construct(MetricsService $metricsService)
    {
        $this->metricsService = $metricsService;
    }

    /**
     * Handle the request.
     */
    public function __invoke(DashboardRequest $request)
    {
        switch (Auth::user()->role) {
            case 'Admin':
                return $this->adminMetrics($request->validated());
            case 'Agent':
                return $this->agentMetrics($request->validated());
            default:
                return $this->userMetrics();
        }
    }

    private function adminMetrics()
    {
        $query = Ticket::query();
        $queryPerAgent = Ticket::join('ticket_agent', 'ticket_agent.ticket_id', 'tickets.id')
            ->groupBy('ticket_agent.agent_id')
            ->addSelect('ticket_agent.agent_id');

        return [
            'count_per_priority' => $this->metricsService->countPerPriority(clone $query),
            'count_per_tag' => $this->metricsService->countPerTag(clone $query),
            'count_per_current_status' => $this->metricsService->countPerCurrentStatus(clone $query),
            'avg_time_to_end_status' => $this->metricsService->avgTimeToEndStatus(clone $query),
            'avg_time_to_end_status_per_agent' => $this->metricsService->avgTimeToEndStatusPerAgent(clone $queryPerAgent),
        ];
    }

    private function agentMetrics()
    {
        $query = Ticket::whereHasAgent(Auth::id());

        return [
            'count_per_priority' => $this->metricsService->countPerPriority(clone $query),
            'count_per_tag' => $this->metricsService->countPerTag(clone $query),
            'count_per_current_status' => $this->metricsService->countPerCurrentStatus(clone $query),
            'avg_time_to_end_status' => $this->metricsService->avgTimeToEndStatus(clone $query),
        ];
    }

    private function userMetrics()
    {
        $query = Ticket::whereOwner(Auth::id());

        return [
            'count_per_priority' => $this->metricsService->countPerPriority(clone $query),
            'count_per_tag' => $this->metricsService->countPerTag(clone $query),
            'count_per_current_status' => $this->metricsService->countPerCurrentStatus(clone $query),
        ];
    }
}
