<?php

namespace Tickets\Providers;

use Illuminate\Support\ServiceProvider;
use Tickets\Console\Commands\ImportTicketsCommand;

class TicketsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');

        if ($this->app->runningInConsole()) {
            $this->commands([
                ImportTicketsCommand::class,
            ]);
        }
    }
}