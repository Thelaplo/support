<?php

namespace Tickets\Rest\Actions;

use Illuminate\Support\Collection;
use Lomkit\Rest\Actions\Action;
use Lomkit\Rest\Http\Requests\RestRequest;

class AssignTicketAction extends Action
{
    public string $uriKey = 'assign-ticket';

    public $targeted = true;

    public function fields(RestRequest $request): array
    {
        return [
            'technician_id' => ['required', 'integer', 'exists:users,id'],
        ];
    }

    public function handle(array $fields, Collection $models): void
    {
        $technicianId = (int) $fields['technician_id'];

        foreach ($models as $ticket) {
            $ticket->update([
                'technician_id' => $technicianId,
                'status' => 'assigned',
            ]);
        }
    }
}
