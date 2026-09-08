<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use Tickets\Models\Comment;
use Tickets\Models\Ticket;

class TicketCommentsTest extends TestCase
{
    use RefreshDatabase;

    protected User $requester;
    protected User $technician;
    protected User $intruder;
    protected Ticket $ticket;
    protected Comment $comment;

    protected function setUp(): void
    {
        parent::setUp();

        $this->requester = User::factory()->create();
        $this->technician = User::factory()->create();
        $this->intruder = User::factory()->create();

        $this->ticket = Ticket::create([
            'requester_id' => $this->requester->id,
            'technician_id' => $this->technician->id,
            'title' => 'Serveur injoignable',
            'description' => 'Erreur 502 sur la passerelle.',
            'status' => 'assigned',
            'priority' => 'critical',
        ]);

        $this->comment = Comment::create([
            'ticket_id' => $this->ticket->id,
            'author_id' => $this->technician->id,
            'body' => 'Redémarrage du service Nginx en cours.',
        ]);
    }

    public function test_le_demandeur_peut_voir_les_commentaires_de_son_ticket(): void
    {
        Sanctum::actingAs($this->requester);

        $response = $this->postJson('/api/comments/search', [
            'search' => [
                'filters' => [
                    ['field' => 'ticket_id', 'operator' => '=', 'value' => $this->ticket->id]
                ]
            ]
        ]);

        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
        $this->assertSame($this->comment->body, $response->json('data.0.body'));
    }

    public function test_un_intrus_ne_voit_aucun_commentaire_dun_ticket_tiers(): void
    {
        Sanctum::actingAs($this->intruder);

        $response = $this->postJson('/api/comments/search', [
            'search' => [
                'filters' => [
                    ['field' => 'ticket_id', 'operator' => '=', 'value' => $this->ticket->id]
                ]
            ]
        ]);

        $response->assertOk();
        $this->assertEmpty($response->json('data'));
    }

    public function test_un_technicien_peut_ajouter_un_commentaire_via_mutation(): void
    {
        Sanctum::actingAs($this->technician);

        $response = $this->postJson('/api/comments/mutate', [
            'mutate' => [
                [
                    'operation' => 'create',
                    'attributes' => [
                        'ticket_id' => $this->ticket->id,
                        'author_id' => $this->technician->id,
                        'body' => 'Patch de sécurité appliqué avec succès.',
                    ]
                ]
            ]
        ]);

        $response->assertOk();

        $this->assertDatabaseHas('comments', [
            'ticket_id' => $this->ticket->id,
            'author_id' => $this->technician->id,
            'body' => 'Patch de sécurité appliqué avec succès.',
        ]);
    }
}
