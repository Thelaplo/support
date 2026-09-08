<?php

namespace Tickets\Rest\Resources;

use App\Models\User;
use Lomkit\Rest\Http\Requests\RestRequest;
use Lomkit\Rest\Http\Resource;

class UserResource extends Resource
{
    public static $model = User::class;

    public function authorizeTo($ability, $model = null): bool
    {
        return true;
    }

    public function authorizeToField($ability, $field, $model = null): bool
    {
        return true;
    }

    public function fields(RestRequest $request): array
    {
        return [
            'id',
            'name',
            'email',
            'created_at',
            'updated_at',
        ];
    }
}