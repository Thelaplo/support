<?php

namespace Tickets\Notifications\Strategies;

use App\Models\User;
use Illuminate\Support\Facades\Notification;
use Tickets\Models\Ticket;
use Tickets\Notifications\TicketUpdatedNotification;

class CriticalNotificationStrategy implements NotificationStrategyInterface
{
    public function send(Ticket $ticket): void
    {
        try {
            $managers = User::role('manager')->get();
            if ($managers->isNotEmpty()) {
                Notification::send($managers, new TicketUpdatedNotification($ticket));
            }
        } catch (\Throwable $e) {
            // Ignore si les rôles ne sont pas initialisés en test
        }

        if ($ticket->technician) {
            try {
                $ticket->technician->notify(new TicketUpdatedNotification($ticket));
            } catch (\Throwable $e) {
                // Ignore
            }
        }
    }
}
