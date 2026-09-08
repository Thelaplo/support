<?php

namespace App\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\JsonSchema\Types\Type;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use Tickets\Models\Ticket;

#[Description('Récupère les détails complets d\'un ticket de support et son historique de commentaires.')]
class GetTicketDetailTool extends Tool
{
    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        $ticketId = $request->get('ticket_id');

        if (! $ticketId) {
            return Response::text(json_encode(['error' => 'Le paramètre ticket_id est obligatoire.'], JSON_PRETTY_PRINT));
        }

        $ticket = Ticket::with(['requester:id,name,email', 'technician:id,name,email', 'comments.user:id,name'])
            ->find($ticketId);

        if (! $ticket) {
            return Response::text(json_encode(['error' => "Ticket #{$ticketId} introuvable."], JSON_PRETTY_PRINT));
        }

        $data = [
            'id' => $ticket->id,
            'title' => $ticket->title,
            'description' => $ticket->description,
            'status' => is_object($ticket->status) ? $ticket->status->value : (string) $ticket->status,
            'priority' => is_object($ticket->priority) ? $ticket->priority->value : (string) $ticket->priority,
            'requester' => [
                'id' => $ticket->requester?->id,
                'name' => $ticket->requester?->name,
                'email' => $ticket->requester?->email,
            ],
            'technician' => $ticket->technician ? [
                'id' => $ticket->technician->id,
                'name' => $ticket->technician->name,
                'email' => $ticket->technician->email,
            ] : null,
            'comments' => $ticket->comments->map(fn ($c) => [
                'id' => $c->id,
                'author' => $c->user?->name,
                'content' => $c->content,
                'created_at' => $c->created_at?->toIso8601String(),
            ])->toArray(),
            'created_at' => $ticket->created_at?->toIso8601String(),
            'updated_at' => $ticket->updated_at?->toIso8601String(),
        ];

        return Response::text(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    /**
     * Get the tool's input schema.
     *
     * @return array<string, Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'ticket_id' => $schema->integer()->description('Identifiant numérique unique du ticket (ID)'),
        ];
    }
}
