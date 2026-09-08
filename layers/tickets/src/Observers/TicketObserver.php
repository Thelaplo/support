<?php

namespace Tickets\Observers;

use Tickets\Models\Ticket;
use Tickets\Services\AutoAssignTicketService;

class TicketObserver
{
    public function creating(Ticket $ticket): void
    {
        if (empty($ticket->technician_id)) {
            app(AutoAssignTicketService::class)->assign($ticket);
        }
    }
}
