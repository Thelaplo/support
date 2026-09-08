<?php

declare(strict_types=1);

namespace Tickets\Notifications\Strategies;

use Tickets\Models\Ticket;

interface TicketNotificationStrategy
{
    public function send(Ticket $ticket): void;
}
