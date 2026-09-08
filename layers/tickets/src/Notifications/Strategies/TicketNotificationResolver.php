<?php

namespace Tickets\Notifications\Strategies;

use Tickets\Models\Ticket;

class TicketNotificationResolver
{
    public function resolve(Ticket|string|object $ticket): NotificationStrategyInterface
    {
        $priority = 'normal';

        if ($ticket instanceof Ticket) {
            $priority = is_object($ticket->priority) ? $ticket->priority->value : $ticket->priority;
        } elseif (is_string($ticket) || is_object($ticket)) {
            $priority = is_object($ticket) && property_exists($ticket, 'value') ? $ticket->value : (string) $ticket;
        }

        if ($priority === 'critical') {
            return new CriticalNotificationStrategy();
        }

        return new StandardNotificationStrategy();
    }
}
