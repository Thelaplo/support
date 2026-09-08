<?php

declare(strict_types=1);

namespace Tickets\Notifications\Strategies;

use Tickets\Models\Ticket;
use Tickets\Notifications\TicketUpdatedNotification;

class StandardNotificationStrategy implements TicketNotificationStrategy
{
    public function send(Ticket $ticket): void
    {
        if ($ticket->technician) {
            $ticket->technician->notify(new TicketUpdatedNotification($ticket));
        }
    }
}
