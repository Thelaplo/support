<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use Tickets\Models\Ticket;

class TicketSoftDeletesTest extends TestCase
{
    use RefreshDatabase;

    public function test_suppression_logique_dun_ticket_et_restauration_via_action(): void
    {
        $technician = User::factory()->create();

        $ticket = Ticket::create([
            'requester_id' => $technician->id,
            'technician_id' => $technician->id,
            'title' => 'Ticket à archiver',
            'description' => 'Sera supprimé logiquement',
            'status' => 'closed',
            'priority' => 'low',
        ]);

        // Suppression logique standard Eloquent
        $ticket->delete();

        $this->assertSoftDeleted('tickets', ['id' => $ticket->id]);

        Sanctum::actingAs($technician);

        // Restauration via l'action Lomkit
        $response = $this->postJson('/api/tickets/actions/restore-ticket', [
            'resources' => [$ticket->id],
            'fields' => [],
        ]);

        $response->assertOk()
            ->assertJson(['data' => ['impacted' => 1]]);

        $this->assertNotSoftDeleted('tickets', ['id' => $ticket->id]);
        $this->assertDatabaseHas('comments', [
            'ticket_id' => $ticket->id,
            'body' => '[Système] Ticket restauré depuis la corbeille.',
        ]);
    }
}
