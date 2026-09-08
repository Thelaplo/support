<?php

namespace Tickets\Actions;

use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Tickets\Enums\TicketPriority;
use Tickets\Enums\TicketStatus;
use Tickets\Models\Ticket;

class ImportTicketsCsvAction
{
    /**
     * @param string $filePath Chemin absolu vers le fichier CSV
     * @param User $defaultRequester Demandeur par défaut si absent
     * @return array{imported: int, errors: array<int, array<string, mixed>>}
     */
    public function execute(string $filePath, User $defaultRequester): array
    {
        $imported = 0;
        $errors = [];

        if (!file_exists($filePath) || !is_readable($filePath)) {
            return [
                'imported' => 0,
                'errors' => [1 => ['file' => 'Fichier introuvable ou illisible.']],
            ];
        }

        $handle = fopen($filePath, 'r');
        if ($handle === false) {
            return [
                'imported' => 0,
                'errors' => [1 => ['file' => "Impossible d'ouvrir le fichier."]],
            ];
        }

        $header = fgetcsv($handle, 1000, ',');
        if (!$header) {
            fclose($handle);
            return ['imported' => 0, 'errors' => []];
        }

        $header = array_map(fn($h) => trim(strtolower((string) $h)), $header);
        $lineNumber = 1;

        while (($row = fgetcsv($handle, 1000, ',')) !== false) {
            $lineNumber++;

            if (count($row) !== count($header)) {
                $errors[$lineNumber] = ['format' => 'Nombre de colonnes incorrect.'];
                continue;
            }

            $data = array_combine($header, array_map('trim', $row));

            $validator = Validator::make($data, [
                'title' => ['required', 'string', 'max:255'],
                'description' => ['required', 'string'],
                'priority' => ['required', Rule::enum(TicketPriority::class)],
            ]);

            if ($validator->fails()) {
                $errors[$lineNumber] = $validator->errors()->toArray();
                continue;
            }

            Ticket::create([
                'title' => $data['title'],
                'description' => $data['description'],
                'priority' => TicketPriority::from($data['priority']),
                'status' => TicketStatus::Open,
                'requester_id' => $defaultRequester->id,
            ]);

            $imported++;
        }

        fclose($handle);

        return [
            'imported' => $imported,
            'errors' => $errors,
        ];
    }
}
