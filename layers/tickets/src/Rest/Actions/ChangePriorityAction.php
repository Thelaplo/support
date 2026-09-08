<?php

namespace Tickets\Rest\Actions;

use Illuminate\Support\Collection;
use Lomkit\Rest\Actions\Action;
use Lomkit\Rest\Http\Requests\RestRequest;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class ChangePriorityAction extends Action
{
    public string $uriKey = 'change-priority';

    public $targeted = true;

    public function fields(RestRequest $request): array
    {
        return [
            'priority' => ['required', 'string', 'in:low,medium,high,critical'],
            'reason' => ['nullable', 'string', 'min:3'],
        ];
    }

    public function handle(array $fields, Collection $models): void
    {
        $userId = auth()->id() ?? auth('sanctum')->id();
        $newPriority = $fields['priority'];
        $reason = $fields['reason'] ?? 'Priorité mise à jour.';

        foreach ($models as $ticket) {
            if ($ticket->technician_id !== $userId && $ticket->requester_id !== $userId) {
                throw new AccessDeniedHttpException("Vous n'êtes pas autorisé à modifier la priorité de ce ticket.");
            }

            $oldPriority = is_object($ticket->priority) ? $ticket->priority->value : (string) $ticket->priority;

            $ticket->update([
                'priority' => $newPriority,
            ]);

            $ticket->comments()->create([
                'author_id' => $userId ?? $ticket->requester_id,
                'body' => sprintf('[Priorité] Passage de %s à %s : %s', $oldPriority, $newPriority, $reason),
            ]);
        }
    }
}
