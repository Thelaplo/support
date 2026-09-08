<?php

declare(strict_types=1);

namespace Tickets\Notifications\Strategies;

use Tickets\Models\Ticket;
use Tickets\Notifications\TicketUpdatedNotification;
use App\Models\User;

class CriticalNotificationStrategy implements TicketNotificationStrategy
{
    public function send(Ticket $ticket): void
    {
        if ($ticket->technician) {
            $ticket->technician->notify(new TicketUpdatedNotification($ticket));
        }

        $managers = User::role("manager")->get();
        foreach ($managers as $manager) {
            $manager->notify(new TicketUpdatedNotification($ticket));
        }
    }
}
