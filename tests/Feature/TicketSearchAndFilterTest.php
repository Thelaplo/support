<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use Tickets\Models\Comment;
use Tickets\Models\Ticket;

class TicketSearchAndFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_recherche_filtree_par_statut_et_chargement_des_relations(): void
    {
        $technician = User::factory()->create();

        // 1 ticket ouvert haute priorité
        $ticketOpen = Ticket::create([
            'requester_id' => $technician->id,
            'technician_id' => $technician->id,
            'title' => 'Panne switch baie réseau',
            'description' => 'Port 24 défaillant',
            'status' => 'open',
            'priority' => 'high',
        ]);

        Comment::create([
            'ticket_id' => $ticketOpen->id,
            'author_id' => $technician->id,
            'body' => 'Test de diagnostic en cours.',
        ]);

        // 1 ticket résolu basse priorité
        Ticket::create([
            'requester_id' => $technician->id,
            'technician_id' => $technician->id,
            'title' => 'Demande de souris sans fil',
            'description' => 'Matériel bureautique',
            'status' => 'resolved',
            'priority' => 'low',
            'resolved_at' => now(),
        ]);

        Sanctum::actingAs($technician);

        // Requête REST Lomkit : filtre sur status = 'open' + eager loading de 'comments'
        $response = $this->postJson('/api/tickets/search', [
            'search' => [
                'filters' => [
                    ['field' => 'status', 'operator' => '=', 'value' => 'open'],
                ],
                'orders' => [
                    ['field' => 'id', 'direction' => 'desc'],
                ],
                'includes' => [
                    ['relation' => 'comments'],
                ],
            ],
        ]);

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $ticketOpen->id)
            ->assertJsonPath('data.0.status', 'open')
            ->assertJsonCount(1, 'data.0.comments');
    }
}
