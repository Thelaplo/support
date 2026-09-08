<?php

namespace Tickets\Rest\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Tickets\Models\Ticket;

class TicketStatsController extends Controller
{
    public function index(): JsonResponse
    {
        $userId = auth()->id() ?? auth('sanctum')->id();

        $query = Ticket::query();

        // Cloisonnement : restreint aux tickets assignés ou demandés
        if ($userId) {
            $query->where(function ($q) use ($userId) {
                $q->where('requester_id', $userId)
                  ->orWhere('technician_id', $userId);
            });
        }

        $total = (clone $query)->count();
        $openCount = (clone $query)->whereIn('status', ['open', 'assigned'])->count();
        $resolvedCount = (clone $query)->where('status', 'resolved')->count();
        $closedCount = (clone $query)->where('status', 'closed')->count();

        $avgResolutionMinutes = (clone $query)
            ->whereNotNull('resolved_at')
            ->get()
            ->avg(fn (Ticket $t) => $t->resolutionDurationInMinutes()) ?? 0;

        return response()->json([
            'data' => [
                'total_tickets' => $total,
                'open_tickets' => $openCount,
                'resolved_tickets' => $resolvedCount,
                'closed_tickets' => $closedCount,
                'average_resolution_minutes' => round($avgResolutionMinutes, 1),
            ],
        ]);
    }
}
