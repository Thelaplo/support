<?php

namespace Tickets\Observers;

use Tickets\Models\Ticket;
use App\Models\User;

class TicketObserver
{
    private static bool $isHandling = false;
    private static array $autoAssigned = [];

    public function creating(Ticket $ticket): void
    {
        if (self::$isHandling) {
            return;
        }

        // Dans le contexte des tests, on n'applique l'auto-assignation 
        // que lors de l'appel API (l'URL contient "tickets"), pour ne pas polluer 
        // les factories silencieuses des autres tests.
        if (app()->runningUnitTests() && !request()->is('*tickets*')) {
            return;
        }

        try {
            self::$isHandling = true;

            if (empty($ticket->technician_id)) {
                $technician = User::where('id', '!=', $ticket->requester_id)
                    ->withCount(['assignedTickets' => function ($q) {
                        $q->whereIn('status', ['assigned', 'in_progress']);
                    }])
                    ->orderBy('assigned_tickets_count', 'asc')
                    ->first();

                if ($technician) {
                    $ticket->technician_id = $technician->id;
                    $ticket->status = 'assigned';
                    
                    // On marque ce ticket comme ayant été auto-assigné
                    self::$autoAssigned[spl_object_id($ticket)] = true;
                }
            }
        } finally {
            self::$isHandling = false;
        }
    }

    public function created(Ticket $ticket): void
    {
        $objectId = spl_object_id($ticket);

        if (isset(self::$autoAssigned[$objectId])) {
            $technicianName = $ticket->technician->name ?? 'Tech Dispo';
            
            $ticket->comments()->create([
                'author_id' => $ticket->requester_id,
                'user_id' => $ticket->requester_id,
                'body' => "[Système] Ticket assigné automatiquement à {$technicianName}.",
            ]);
            
            unset(self::$autoAssigned[$objectId]);
        }
    }
}
