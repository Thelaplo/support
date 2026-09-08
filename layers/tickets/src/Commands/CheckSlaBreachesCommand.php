<?php

namespace Tickets\Commands;

use Illuminate\Console\Command;
use Tickets\Models\Ticket;

class CheckSlaBreachesCommand extends Command
{
    protected $signature = 'tickets:check-sla';

    protected $description = 'Vérifie les tickets en cours et alerte sur les dépassements de SLA';

    public function handle(): int
    {
        $openTickets = Ticket::whereIn('status', ['open', 'assigned'])
            ->with(['requester', 'technician'])
            ->get();

        $breachedTickets = $openTickets->filter(fn (Ticket $t) => $t->isSlaBreached());

        if ($breachedTickets->isEmpty()) {
            $this->info('Aucun dépassement de SLA détecté.');
            return self::SUCCESS;
        }

        $this->warn(sprintf('%d ticket(s) en dépassement de SLA détecté(s) !', $breachedTickets->count()));

        $rows = $breachedTickets->map(function (Ticket $t) {
            $priority = is_object($t->priority) ? $t->priority->value : (string) $t->priority;
            return [
                'ID' => $t->id,
                'Titre' => $t->title,
                'Priorité' => $priority,
                'Créé le' => $t->created_at->format('d/m/Y H:i'),
                'Technicien' => $t->technician->name ?? 'Non assigné',
            ];
        });

        $this->table(['ID', 'Titre', 'Priorité', 'Créé le', 'Technicien'], $rows);

        return self::FAILURE;
    }
}
