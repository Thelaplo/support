<?php

namespace Tickets\Services;

use App\Models\User;
use Tickets\Models\Ticket;
use Tickets\Notifications\Strategies\TicketNotificationResolver;
use Tickets\Events\TicketUpdatedEvent;

class AutoAssignTicketService
{
    public function assign(Ticket $ticket): void
    {
        // Chercher l'utilisateur avec le moins de tickets 'assigned' ou 'in_progress', qui n'est pas le demandeur
        $technician = User::where('id', '!=', $ticket->requester_id)
            ->withCount(['assignedTickets' => function ($q) {
                $q->whereIn('status', ['assigned', 'in_progress']);
            }])
            ->orderBy('assigned_tickets_count', 'asc')
            ->first();

        if ($technician) {
            $ticket->technician_id = $technician->id;
            $ticket->status = 'assigned';
            $ticket->save();

            // Ajouter le commentaire système après création
            Ticket::created(function (Ticket $createdTicket) use ($technician) {
                if ($createdTicket->id === $createdTicket->getKey()) {
                    $createdTicket->comments()->create([
                        'author_id' => $technician->id,
                        'body' => "[Système] Ticket assigné automatiquement à {$technician->name}.",
                    ]);
                }
            });

            // Déclencher la stratégie de notification selon la priorité (Extension E3)
            $strategy = (new TicketNotificationResolver())->resolve($ticket->priority);
            $strategy->send($ticket);

            // Diffuser l'événement en temps réel (Extension E5)
            event(new TicketUpdatedEvent($ticket));
        }
    }
}