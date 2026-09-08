<?php

declare(strict_types=1);

namespace Tickets\Enums;

use Tickets\Notifications\Strategies\TicketNotificationStrategy;
use Tickets\Notifications\Strategies\StandardNotificationStrategy;
use Tickets\Notifications\Strategies\CriticalNotificationStrategy;

enum TicketPriority: string
{
    case Low = "low";
    case Normal = "normal";
    case High = "high";
    case Critical = "critical";

    public function targetHours(): int
    {
        return match($this) {
            self::Low => 72,
            self::Normal => 24,
            self::High => 8,
            self::Critical => 2,
        };
    }

    public function resolveNotificationStrategy(): TicketNotificationStrategy
    {
        return match($this) {
            self::Low, self::Normal => new StandardNotificationStrategy(),
            self::High, self::Critical => new CriticalNotificationStrategy(),
        };
    }
}
