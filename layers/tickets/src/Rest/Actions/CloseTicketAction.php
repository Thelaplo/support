<?php

namespace Tickets\Rest\Actions;

use Illuminate\Support\Collection;
use Lomkit\Rest\Actions\Action;
use Lomkit\Rest\Http\Requests\RestRequest;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class CloseTicketAction extends Action
{
    public string $uriKey = 'close-ticket';

    public $targeted = true;

    public function fields(RestRequest $request): array
    {
        return [];
    }

    public function handle(array $fields, Collection $models): void
    {
        $userId = auth()->id() ?? auth('sanctum')->id();

        foreach ($models as $ticket) {
            $status = is_object($ticket->status) ? $ticket->status->value : (string) $ticket->status;

            if ($status !== 'resolved') {
                throw new UnprocessableEntityHttpException("Un ticket doit d'abord être résolu pour être fermé définitivement.");
            }

            $ticket->update([
                'status' => 'closed',
            ]);

            $ticket->comments()->create([
                'author_id' => $userId ?? $ticket->requester_id,
                'body' => '[Clôture] Ticket fermé définitivement.',
            ]);
        }
    }
}
