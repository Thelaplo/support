<?php

namespace Tickets\Rest\Resources;

use Lomkit\Rest\Http\Requests\RestRequest;
use Lomkit\Rest\Http\Resource;
use Lomkit\Rest\Relations\BelongsTo;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Tickets\Models\Comment;
use Tickets\Models\Ticket;

class CommentResource extends Resource
{
    public static $model = Comment::class;

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

        if ($userId) {
            $query->whereHas('ticket', function ($q) use ($userId) {
                $q->where('requester_id', $userId)
                  ->orWhere('technician_id', $userId);
            });
        }
    }

    public function fields(RestRequest $request): array
    {
        return [
            'id',
            'ticket_id',
            'author_id',
            'body',
            'created_at',
            'updated_at',
        ];
    }

    public function relations(RestRequest $request): array
    {
        return [
            BelongsTo::make('ticket', TicketResource::class),
            BelongsTo::make('author', UserResource::class),
        ];
    }
}
