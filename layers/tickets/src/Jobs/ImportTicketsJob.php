<?php

declare(strict_types=1);

namespace Tickets\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Tickets\Models\Ticket;
use Tickets\Enums\TicketPriority;
use Tickets\Enums\TicketStatus;
use App\Models\User;

class ImportTicketsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public string $filePath)
    {
    }

    public function handle(): void
    {
        if (!file_exists($this->filePath)) {
            return;
        }

        $handle = fopen($this->filePath, "r");
        $header = fgetcsv($handle); // Lecture des en-têtes (title, description, priority, requester_email)

        $rowNumber = 1;
        $report = [
            "read" => 0,
            "created" => 0,
            "rejected" => [],
        ];

        while (($data = fgetcsv($handle)) !== false) {
            $rowNumber++;
            $report["read"]++;

            // Normalisation et association des données du CSV
            $row = array_combine($header, $data);

            // Validation simple de la ligne (donnée invalide rejetée sans casser le reste)
            if (empty($row["title"]) || empty($row["requester_email"])) {
                $report["rejected"][] = [
                    "line" => $rowNumber,
                    "reason" => "Missing required fields (title or requester_email)",
                ];
                continue;
            }

            $requester = User::where("email", $row["requester_email"])->first();
            if (!$requester) {
                $report["rejected"][] = [
                    "line" => $rowNumber,
                    "reason" => "Requester email not found: " . $row["requester_email"],
                ];
                continue;
            }

            // Création sécurisée du ticket
            Ticket::create([
                "title" => $row["title"],
                "description" => $row["description"] ?? "",
                "priority" => $row["priority"] ?? TicketPriority::Normal->value,
                "status" => TicketStatus::Open->value,
                "requester_id" => $requester->id,
            ]);

            $report["created"]++;
        }

        fclose($handle);

        // On peut stocker ou logger le rapport d’import
        info("CSV Import completed.", $report);
    }
}
