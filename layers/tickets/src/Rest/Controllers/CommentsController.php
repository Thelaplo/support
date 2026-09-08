<?php

namespace Tickets\Rest\Controllers;

use Lomkit\Rest\Http\Controllers\Controller;
use Lomkit\Rest\Http\Resource;
use Tickets\Rest\Resources\CommentResource;

class CommentsController extends Controller
{
    /**
     * The resource the controller operates on.
     */
    public static $resource = CommentResource::class;
}
