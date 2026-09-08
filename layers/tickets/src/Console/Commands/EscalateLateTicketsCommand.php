<?php

declare(strict_types=1);

namespace Tickets\Console\Commands;

use Illuminate\Console\Command;
use Tickets\Models\Ticket;
use Tickets\Enums\TicketPriority;
use Tickets\Enums\TicketStatus;
use Illuminate\Support\Facades\Notification;
use App\Models\User;

class EscalateLateTicketsCommand extends Command
{
    protected $signature = 'tickets:escalate';

    protected $description = 'Examine les tickets non résolus, escalade ceux en retard et notifie le responsable.';

    public function handle(): int
    {
        $examinedCount = 0;
        $escalatedCount = 0;

        $tickets = Ticket::query()
            ->whereNotIn('status', [TicketStatus::Resolved->value, TicketStatus::Closed->value])
            ->where(function ($q) {
                $q->where(function ($sub) {
                    $sub->where('priority', TicketPriority::Low->value)
                        ->where('created_at', '<=', now()->subHours(72));
                })->orWhere(function ($sub) {
                    $sub->where('priority', TicketPriority::Normal->value)
                        ->where('created_at', '<=', now()->subHours(24));
                })->orWhere(function ($sub) {
                    $sub->where('priority', TicketPriority::High->value)
                        ->where('created_at', '<=', now()->subHours(8));
                })->orWhere(function ($sub) {
                    $sub->where('priority', TicketPriority::Critical->value)
                        ->where('created_at', '<=', now()->subHours(2));
                });
            })
            ->get();

        $examinedCount = $tickets->count();

        foreach ($tickets as $ticket) {
            if ($ticket->priority === TicketPriority::High) {
                $ticket->update(['priority' => TicketPriority::Critical->value]);
                $this->notifyManager($ticket);
                $escalatedCount++;
            } elseif ($ticket->priority === TicketPriority::Normal) {
                $ticket->update(['priority' => TicketPriority::High->value]);
                $this->notifyManager($ticket);
                $escalatedCount++;
            } elseif ($ticket->priority === TicketPriority::Low) {
                $ticket->update(['priority' => TicketPriority::Normal->value]);
                $this->notifyManager($ticket);
                $escalatedCount++;
            } elseif ($ticket->priority === TicketPriority::Critical) {
                $this->notifyManager($ticket);
            }
        }

        $this->info("Examen terminé : {$examinedCount} tickets examinés, {$escalatedCount} tickets escaladés.");

        return self::SUCCESS;
    }

    private function notifyManager(Ticket $ticket): void
    {
        // Récupération sécurisée des utilisateurs ayant le rôle manager via Spatie
        $managers = User::role('manager')->get();

        if ($managers->isNotEmpty()) {
            Notification::send($managers, new \Tickets\Notifications\TicketEscalatedNotification($ticket));
        }
    }
}
