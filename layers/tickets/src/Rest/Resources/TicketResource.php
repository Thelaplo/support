<?php

namespace Tickets\Rest\Resources;

use Lomkit\Rest\Http\Requests\RestRequest;
use Lomkit\Rest\Http\Resource;
use Lomkit\Rest\Relations\BelongsTo;
use Lomkit\Rest\Relations\HasMany;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Tickets\Models\Ticket;
use Tickets\Rest\Actions\AssignTicketAction;
use Tickets\Rest\Actions\ChangePriorityAction;
use Tickets\Rest\Actions\CloseTicketAction;
use Tickets\Rest\Actions\ReopenTicketAction;
use Tickets\Rest\Actions\ResolveTicketAction;
use Tickets\Rest\Actions\RestoreTicketAction;

class TicketResource extends Resource
{
    public static $model = Ticket::class;

    public function isAuthorizationCacheEnabled(): bool
    {
        return false;
    }

    public function authorizeTo($ability, $model = null): bool
    {
        return true;
    }

    public function authorizeToField($ability, $field, $model = null): bool
    {
        return true;
    }

    public function searchQuery(RestRequest $request, $query)
    {
        $userId = auth()->id() ?? auth('sanctum')->id();

        // Permet à Lomkit de résoudre les modèles même s'ils sont dans la corbeille (Soft Delete)
        $query->withTrashed();

        if ($userId) {
            $targetedResources = $request->input('resources');
            if (is_array($targetedResources) && !empty($targetedResources)) {
                $hasUnauthorizedResource = Ticket::withTrashed()
                    ->whereIn('id', $targetedResources)
                    ->where(function ($q) use ($userId) {
                        $q->where('requester_id', '!=', $userId)
                          ->where('technician_id', '!=', $userId);
                    })
                    ->exists();

                if ($hasUnauthorizedResource) {
                    throw new AccessDeniedHttpException("Vous n'avez pas accès à ce ticket.");
                }
            }

            $query->where(function ($q) use ($userId) {
                $q->where('requester_id', $userId)
                  ->orWhere('technician_id', $userId);
            });
        }
    }

    public function fields(RestRequest $request): array
    {
        return [
            'id',
            'requester_id',
            'technician_id',
            'title',
            'description',
            'status',
            'priority',
            'resolved_at',
            'created_at',
            'updated_at',
            'deleted_at',
        ];
    }

    public function relations(RestRequest $request): array
    {
        return [
            BelongsTo::make('requester', UserResource::class),
            BelongsTo::make('technician', UserResource::class),
            HasMany::make('comments', CommentResource::class),
        ];
    }

    public function actions(RestRequest $request): array
    {
        return [
            AssignTicketAction::make(),
            ChangePriorityAction::make(),
            ResolveTicketAction::make(),
            ReopenTicketAction::make(),
            CloseTicketAction::make(),
            RestoreTicketAction::make(),
        ];
    }
}
