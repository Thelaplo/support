<?php

declare(strict_types=1);

namespace Tickets\Notifications\Strategies;

use Tickets\Enums\TicketPriority;

class TicketNotificationResolver
{
    protected array $strategies = [
        TicketPriority::Low->value => StandardNotificationStrategy::class,
        TicketPriority::Normal->value => StandardNotificationStrategy::class,
        TicketPriority::High->value => CriticalNotificationStrategy::class,
        TicketPriority::Critical->value => CriticalNotificationStrategy::class,
    ];

    public function resolve(TicketPriority $priority): TicketNotificationStrategy
    {
        $strategyClass = $this->strategies[$priority->value] ?? StandardNotificationStrategy::class;

        return new $strategyClass();
    }
}
