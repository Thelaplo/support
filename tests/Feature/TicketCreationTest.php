<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use Tickets\Models\Ticket;

class TicketCreationTest extends TestCase
{
    use RefreshDatabase;

    public function test_un_utilisateur_peut_creer_un_ticket_via_mutation(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/tickets/mutate', [
            'mutate' => [
                [
                    'operation' => 'create',
                    'attributes' => [
                        'requester_id' => $user->id,
                        'title' => 'Écran bleu sur le poste d accueil',
                        'description' => 'Le PC reboot en boucle au démarrage de Windows.',
                        'status' => 'open',
                        'priority' => 'high',
                    ]
                ]
            ]
        ]);

        $response->assertOk();

        $this->assertDatabaseHas('tickets', [
            'requester_id' => $user->id,
            'title' => 'Écran bleu sur le poste d accueil',
            'status' => 'open',
            'priority' => 'high',
        ]);
    }
}
