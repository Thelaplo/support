<?php

namespace Tickets;

use App\Policies\TicketPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Tickets\Commands\CheckSlaBreachesCommand;
use Tickets\Models\Ticket;
use Tickets\Observers\TicketObserver;

class TicketsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::policy(Ticket::class, TicketPolicy::class);
        Ticket::observe(TicketObserver::class);

        if ($this->app->runningInConsole()) {
            $this->commands([
                CheckSlaBreachesCommand::class,
                \Tickets\Console\Commands\EscalateLateTicketsCommand::class,
            ]);
        }
    }
}
