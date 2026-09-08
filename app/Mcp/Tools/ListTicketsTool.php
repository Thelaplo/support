<?php

namespace App\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\JsonSchema\Types\Type;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use Tickets\Models\Ticket;

#[Description('Liste les tickets de support avec possibilité de filtrer par statut et priorité.')]
class ListTicketsTool extends Tool
{
    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        $status = $request->get('status');
        $priority = $request->get('priority');
        $limit = (int) ($request->get('limit') ?: 10);

        $query = Ticket::query()
            ->with(['requester:id,name,email', 'technician:id,name,email'])
            ->withCount('comments');

        if ($status) {
            $query->where('status', $status);
        }

        if ($priority) {
            $query->where('priority', $priority);
        }

        $tickets = $query->latest()
            ->limit(min($limit, 50))
            ->get()
            ->map(fn (Ticket $ticket) => [
                'id' => $ticket->id,
                'title' => $ticket->title,
                'status' => is_object($ticket->status) ? $ticket->status->value : (string) $ticket->status,
                'priority' => is_object($ticket->priority) ? $ticket->priority->value : (string) $ticket->priority,
                'requester' => $ticket->requester?->name,
                'technician' => $ticket->technician?->name ?? 'Non assigné',
                'comments_count' => $ticket->comments_count,
                'created_at' => $ticket->created_at?->toIso8601String(),
            ]);

        return Response::text(
            json_encode([
                'total' => $tickets->count(),
                'tickets' => $tickets->toArray(),
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );
    }

    /**
     * Get the tool's input schema.
     *
     * @return array<string, Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'status' => $schema->string()->description('Filtrer par statut (open, assigned, in_progress, resolved, closed)'),
            'priority' => $schema->string()->description('Filtrer par priorité (low, normal, high, urgent)'),
            'limit' => $schema->integer()->description('Nombre maximum de tickets à retourner (défaut 10)'),
        ];
    }
}
