<?php

namespace Tickets;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Tickets\Commands\CheckSlaBreachesCommand;
use Tickets\Models\Ticket;
use Tickets\Policies\TicketPolicy;

class TicketsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::policy(Ticket::class, TicketPolicy::class);

        if ($this->app->runningInConsole()) {
            $this->commands([
                CheckSlaBreachesCommand::class,
            ]);
        }
    }
}
