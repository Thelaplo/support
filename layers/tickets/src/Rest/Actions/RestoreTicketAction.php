<?php

namespace Tickets\Rest\Actions;

use Illuminate\Support\Collection;
use Lomkit\Rest\Actions\Action;
use Lomkit\Rest\Http\Requests\RestRequest;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class RestoreTicketAction extends Action
{
    public string $uriKey = 'restore-ticket';

    public $targeted = true;

    public function fields(RestRequest $request): array
    {
        return [];
    }

    public function handle(array $fields, Collection $models): void
    {
        $userId = auth()->id() ?? auth('sanctum')->id();

        foreach ($models as $ticket) {
            if ($ticket->technician_id !== $userId && $ticket->requester_id !== $userId) {
                throw new AccessDeniedHttpException("Vous n'êtes pas autorisé à restaurer ce ticket.");
            }

            $ticket->restore();

            $ticket->comments()->create([
                'author_id' => $userId ?? $ticket->requester_id,
                'body' => '[Système] Ticket restauré depuis la corbeille.',
            ]);
        }
    }
}
