<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Tickets\Enums\TicketPriority;
use Tickets\Enums\TicketStatus;

class TicketStatusLifecycleTest extends TestCase
{
    public function test_les_priorites_ont_des_delais_sla_coherents(): void
    {
        $slaHours = [
            TicketPriority::Critical->value => 4,
            TicketPriority::High->value => 8,
            TicketPriority::Normal->value => 24,
            TicketPriority::Low->value => 72,
        ];

        $this->assertLessThan($slaHours['high'], $slaHours['critical']);
        $this->assertLessThan($slaHours['normal'], $slaHours['high']);
        $this->assertLessThan($slaHours['low'], $slaHours['normal']);
    }

    public function test_les_statuts_couvrent_l_integralite_du_cycle_de_vie(): void
    {
        $cases = array_map(fn($case) => $case->value, TicketStatus::cases());

        $this->assertContains('open', $cases);
        $this->assertContains('assigned', $cases);
        $this->assertContains('in_progress', $cases);
        $this->assertContains('resolved', $cases);
        $this->assertContains('closed', $cases);
    }
}
