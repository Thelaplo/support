<?php

namespace Tickets\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Tickets\Events\TicketStatusUpdated;
use Tickets\Listeners\SendTicketStatusNotification;

class TicketsEventServiceProvider extends ServiceProvider
{
    protected $listen = [
        TicketStatusUpdated::class => [
            SendTicketStatusNotification::class,
        ],
    ];
}
