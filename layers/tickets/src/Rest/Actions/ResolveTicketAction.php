<?php

namespace Tickets\Rest\Actions;

use Illuminate\Support\Collection;
use Lomkit\Rest\Actions\Action;
use Lomkit\Rest\Http\Requests\RestRequest;
use Tickets\Events\TicketStatusUpdated;

class ResolveTicketAction extends Action
{
    public string $uriKey = 'resolve-ticket';

    public $targeted = true;

    public function fields(RestRequest $request): array
    {
        return [
            'resolution_note' => ['required', 'string', 'min:5'],
        ];
    }

    public function handle(array $fields, Collection $models): void
    {
        $userId = auth()->id() ?? auth('sanctum')->id();
        $note = $fields['resolution_note'] ?? 'Ticket résolu.';

        foreach ($models as $ticket) {
            $previousStatus = is_object($ticket->status) ? $ticket->status->value : (string) $ticket->status;

            $ticket->update([
                'status' => 'resolved',
                'resolved_at' => now(),
            ]);

            $ticket->comments()->create([
                'author_id' => $userId ?? $ticket->requester_id,
                'body' => '[Résolution] ' . $note,
            ]);

            event(new TicketStatusUpdated($ticket, $previousStatus, 'resolved'));
        }
    }
}
