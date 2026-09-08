<?php

namespace Tickets\Notifications\Strategies;

use Tickets\Models\Ticket;

interface NotificationStrategyInterface
{
    public function send(Ticket $ticket): void;
}
