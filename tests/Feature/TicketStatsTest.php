<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use Tickets\Models\Ticket;

class TicketStatsTest extends TestCase
{
    use RefreshDatabase;

    public function test_calcul_des_statistiques_tickets(): void
    {
        $technician = User::factory()->create();

        // 1 ticket ouvert
        Ticket::create([
            'requester_id' => $technician->id,
            'technician_id' => $technician->id,
            'title' => 'Ticket en cours',
            'description' => 'Test description',
            'status' => 'assigned',
            'priority' => 'high',
        ]);

        // 1 ticket résolu : créé à T-60min, résolu à T-10min => durée 50 min
        $resolvedTicket = Ticket::create([
            'requester_id' => $technician->id,
            'technician_id' => $technician->id,
            'title' => 'Ticket résolu',
            'description' => 'Test description',
            'status' => 'resolved',
            'priority' => 'high',
            'resolved_at' => now()->subMinutes(10),
        ]);

        // Force la date en base sans écrasement automatique par Eloquent
        $resolvedTicket->timestamps = false;
        $resolvedTicket->created_at = now()->subMinutes(60);
        $resolvedTicket->save();

        Sanctum::actingAs($technician);

        $response = $this->getJson('/api/tickets-stats');

        $response->assertOk()
            ->assertJsonPath('data.total_tickets', 2)
            ->assertJsonPath('data.open_tickets', 1)
            ->assertJsonPath('data.resolved_tickets', 1)
            ->assertJsonPath('data.average_resolution_minutes', 50);
    }

    public function test_detection_depassement_sla(): void
    {
        // Ticket critique créé il y a 5 heures (seuil critique = 4h) sans résolution
        $ticket = Ticket::create([
            'requester_id' => User::factory()->create()->id,
            'title' => 'Incident majeur',
            'description' => 'Crash total',
            'status' => 'open',
            'priority' => 'critical',
        ]);

        $ticket->timestamps = false;
        $ticket->created_at = now()->subHours(5);
        $ticket->save();

        $this->assertTrue($ticket->fresh()->isSlaBreached());
    }
}
