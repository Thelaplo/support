<?php

declare(strict_types=1);

namespace Tickets\Console\Commands;

use Illuminate\Console\Command;
use Tickets\Jobs\ImportTicketsJob;

class ImportTicketsCommand extends Command
{
    protected $signature = "tickets:import {file : Path to the CSV file}";

    protected $description = "Import tickets in bulk from a CSV file with error reporting";

    public function handle(): int
    {
        $filePath = $this->argument("file");

        if (!file_exists($filePath)) {
            $this->error("File not found: {$filePath}");
            return self::FAILURE;
        }

        $this->info("Starting tickets import from: {$filePath}");

        // Dispatch ou exécution synchrone pour récupérer le rapport immédiat
        (new ImportTicketsJob($filePath))->handle();

        $this->info("Import process finished. Check logs for details.");

        return self::SUCCESS;
    }
}
