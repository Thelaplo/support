<?php

namespace Tickets\Listeners;

use Tickets\Events\TicketStatusUpdated;
use Tickets\Notifications\TicketStatusNotification;

class SendTicketStatusNotification
{
    public function handle(TicketStatusUpdated $event): void
    {
        $ticket = $event->ticket;
        $requester = $ticket->requester;

        if ($requester) {
            $requester->notify(new TicketStatusNotification($ticket, $event->newStatus));
        }
    }
}
