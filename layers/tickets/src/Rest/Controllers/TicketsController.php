<?php

namespace Tickets\Rest\Controllers;

use Lomkit\Rest\Http\Controllers\Controller;
use Tickets\Rest\Resources\TicketResource;

class TicketsController extends Controller
{
    public static $resource = TicketResource::class;
}