<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use Tickets\Models\Ticket;

class TicketAutoAssignmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_un_ticket_cree_sans_technicien_est_automatiquement_assigne(): void
    {
        $requester = User::factory()->create(['name' => 'Demandeur']);
        $availableTechnician = User::factory()->create(['name' => 'Tech Dispo']);
        $busyTechnician = User::factory()->create(['name' => 'Tech Occupé']);

        // Tech Occupé a déjà 2 tickets en cours (priorité 'normal')
        Ticket::create([
            'requester_id' => $requester->id,
            'technician_id' => $busyTechnician->id,
            'title' => 'Panne 1',
            'description' => 'Desc 1',
            'status' => 'assigned',
            'priority' => 'normal',
        ]);
        Ticket::create([
            'requester_id' => $requester->id,
            'technician_id' => $busyTechnician->id,
            'title' => 'Panne 2',
            'description' => 'Desc 2',
            'status' => 'assigned',
            'priority' => 'normal',
        ]);

        Sanctum::actingAs($requester);

        // Création d'un nouveau ticket sans technicien
        $response = $this->postJson('/api/tickets/mutate', [
            'mutate' => [
                [
                    'operation' => 'create',
                    'attributes' => [
                        'requester_id' => $requester->id,
                        'title' => 'Problème de messagerie Outlook',
                        'description' => 'Impossible de synchroniser la boîte.',
                        'status' => 'open',
                        'priority' => 'high',
                    ]
                ]
            ]
        ]);

        $response->assertOk();

        // Le ticket doit avoir été attribué au technicien le moins chargé
        $newTicket = Ticket::where('title', 'Problème de messagerie Outlook')->first();
        $this->assertNotNull($newTicket);
        $this->assertSame($availableTechnician->id, $newTicket->technician_id);

        $statusValue = is_object($newTicket->status) ? $newTicket->status->value : $newTicket->status;
        $this->assertSame('assigned', $statusValue);

        $this->assertDatabaseHas('comments', [
            'ticket_id' => $newTicket->id,
            'body' => '[Système] Ticket assigné automatiquement à Tech Dispo.',
        ]);
    }
}
