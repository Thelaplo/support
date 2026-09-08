<?php

use App\Providers\AppServiceProvider;

return [
    Tickets\TicketsServiceProvider::class,
    Tickets\Providers\TicketsEventServiceProvider::class,
    AppServiceProvider::class,
    Tickets\Providers\TicketsServiceProvider::class,
];
