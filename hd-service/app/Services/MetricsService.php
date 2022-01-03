<?php

namespace App\Services;

use App\Models\Status;
use App\Models\Ticket;
use Illuminate\Database\Eloquent\Builder;

class MetricsService
{
    public function countPerPriority(Builder $query): array
    {
        return $query
            ->select('priority')
            ->selectRaw('COUNT(id) AS total')
            ->groupBy('priority')
            ->get()
            ->mapWithKeys(function ($row) {
                return [(int) $row['priority'] => (int) $row['total']];
            })
            ->toArray();
    }

    public function countPerTag(Builder $query): array
    {
        return $query->selectRaw('taggables.tag_id AS tag_id')
            ->selectRaw('COUNT(tickets.id) AS total')
            ->leftJoin('taggables', function ($join) {
                return $join->on('taggables.taggable_id', 'tickets.id')
                    ->on('taggables.taggable_type', Ticket::class);
            })
            ->groupBy('taggables.tag_id')
            ->get()
            ->mapWithKeys(function ($row) {
                return [(int) $row['tag_id'] => (int) $row['total']];
            })
            ->toArray();
    }

    public function countPerCurrentStatus(Builder $query): array
    {
        return $query->selectRaw('current_status.name AS name')
            ->selectRaw('COUNT(tickets.id) AS total')
            ->leftJoinSub(
                Status::select('ticket_id', 'name')
                    ->selectRaw('MAX(id) as id')
                    ->groupBy('ticket_id'),
                'current_status',
                function ($join) {
                    $join->on('tickets.id', '=', 'current_status.ticket_id');
                }
            )
            ->groupBy('current_status.name')
            ->get()
            ->mapWithKeys(function ($row) {
                return [$row['name'] => (int) $row['total']];
            })
            ->toArray();
    }

    private function queryAvgTimeToEndStatus(Builder $query): Builder
    {
        return $query->leftJoinSub(
            Status::select('ticket_id', 'name')
                ->selectRaw('MAX(created_at) as created_at')
                ->groupBy('ticket_id'),
            'current_status',
            function ($join) {
                $join->on('tickets.id', '=', 'current_status.ticket_id');
            }
        )
            ->whereIn('current_status.name', config('hd.end_statuses'))
            ->selectRaw('AVG(ROUND((JULIANDAY(current_status.created_at) - JULIANDAY(tickets.created_at)) * 86400)) AS avg');
    }

    public function avgTimeToEndStatus(Builder $query): int
    {
        return (int) $this->queryAvgTimeToEndStatus($query)
            ->get()
            ->toArray()[0]['avg'];
    }

    public function avgTimeToEndStatusPerAgent(Builder $query): array
    {
        return $this->queryAvgTimeToEndStatus($query)
            ->get()
            ->mapWithKeys(function ($row) {
                return [(int) $row['agent_id'] => (int) $row['avg']];
            })
            ->toArray();
    }
}
