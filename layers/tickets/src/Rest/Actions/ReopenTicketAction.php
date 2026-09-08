<?php

namespace Tickets\Rest\Actions;

use Illuminate\Support\Collection;
use Lomkit\Rest\Actions\Action;
use Lomkit\Rest\Http\Requests\RestRequest;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Tickets\Events\TicketStatusUpdated;

class ReopenTicketAction extends Action
{
    public string $uriKey = 'reopen-ticket';

    public $targeted = true;

    public function fields(RestRequest $request): array
    {
        return [
            'reason' => ['required', 'string', 'min:5'],
        ];
    }

    public function handle(array $fields, Collection $models): void
    {
        $userId = auth()->id() ?? auth('sanctum')->id();
        $reason = $fields['reason'];

        foreach ($models as $ticket) {
            $status = is_object($ticket->status) ? $ticket->status->value : (string) $ticket->status;

            if ($status !== 'resolved' && $status !== 'closed') {
                throw new UnprocessableEntityHttpException("Seul un ticket résolu ou fermé peut être rouvert.");
            }

            $ticket->update([
                'status' => 'open',
                'resolved_at' => null,
            ]);

            $ticket->comments()->create([
                'author_id' => $userId ?? $ticket->requester_id,
                'body' => '[Réouverture] ' . $reason,
            ]);

            event(new TicketStatusUpdated($ticket, $status, 'open'));
        }
    }
}
