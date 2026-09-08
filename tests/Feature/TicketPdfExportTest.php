<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use Tickets\Models\Ticket;

class TicketPdfExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_le_demandeur_peut_telecharger_le_rapport_pdf(): void
    {
        $requester = User::factory()->create();
        $technician = User::factory()->create();

        $ticket = Ticket::create([
            'requester_id' => $requester->id,
            'technician_id' => $technician->id,
            'title' => 'Panne switch baie réseau',
            'description' => 'Voyants au rouge sur port uplink.',
            'status' => 'resolved',
            'priority' => 'high',
            'resolved_at' => now(),
        ]);

        Sanctum::actingAs($requester);

        $response = $this->get("/api/tickets/{$ticket->id}/export-pdf");

        $response->assertOk();
        $this->assertSame('application/pdf', $response->headers->get('content-type'));
        $this->assertStringContainsString(
            "rapport-intervention-ticket-{$ticket->id}.pdf",
            $response->headers->get('content-disposition')
        );
    }

    public function test_un_intrus_ne_peut_pas_telecharger_le_rapport_pdf(): void
    {
        $requester = User::factory()->create();
        $intruder = User::factory()->create();

        $ticket = Ticket::create([
            'requester_id' => $requester->id,
            'title' => 'Panne switch baie réseau',
            'description' => 'Voyants au rouge sur port uplink.',
            'status' => 'open',
            'priority' => 'high',
        ]);

        Sanctum::actingAs($intruder);

        $response = $this->getJson("/api/tickets/{$ticket->id}/export-pdf");

        $response->assertForbidden();
    }
}
