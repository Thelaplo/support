<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use Tickets\Models\Ticket;

class TicketActionsTest extends TestCase
{
    use RefreshDatabase;

    protected User $requester;
    protected User $technician;
    protected User $newTechnician;
    protected User $intruder;
    protected Ticket $ticket;

    protected function setUp(): void
    {
        parent::setUp();

        $this->requester = User::factory()->create();
        $this->technician = User::factory()->create();
        $this->newTechnician = User::factory()->create();
        $this->intruder = User::factory()->create();

        $this->ticket = Ticket::create([
            'requester_id' => $this->requester->id,
            'technician_id' => $this->technician->id,
            'title' => 'Panne imprimante',
            'description' => 'Impossible d imprimer les bordereaux.',
            'status' => 'open',
            'priority' => 'high',
        ]);
    }

    public function test_assignation_dun_nouveau_technicien(): void
    {
        Sanctum::actingAs($this->technician);

        $response = $this->postJson('/api/tickets/actions/assign-ticket', [
            'resources' => [$this->ticket->id],
            'fields' => [
                ['name' => 'technician_id', 'value' => $this->newTechnician->id]
            ],
        ]);

        $response->assertOk()
            ->assertJson(['data' => ['impacted' => 1]]);

        $this->ticket->refresh();
        $this->assertSame($this->newTechnician->id, $this->ticket->technician_id);

        $statusValue = is_object($this->ticket->status) ? $this->ticket->status->value : $this->ticket->status;
        $this->assertSame('assigned', $statusValue);
    }

    public function test_changement_de_priorite_avec_commentaire(): void
    {
        Sanctum::actingAs($this->technician);

        $response = $this->postJson('/api/tickets/actions/change-priority', [
            'resources' => [$this->ticket->id],
            'fields' => [
                ['name' => 'priority', 'value' => 'critical'],
                ['name' => 'reason', 'value' => 'Impacte l ensemble de la production.']
            ],
        ]);

        $response->assertOk()
            ->assertJson(['data' => ['impacted' => 1]]);

        $this->ticket->refresh();

        $priorityValue = is_object($this->ticket->priority) ? $this->ticket->priority->value : $this->ticket->priority;
        $this->assertSame('critical', $priorityValue);

        $this->assertDatabaseHas('comments', [
            'ticket_id' => $this->ticket->id,
            'author_id' => $this->technician->id,
        ]);
    }

    public function test_un_technicien_assigne_peut_resoudre_un_ticket(): void
    {
        Sanctum::actingAs($this->technician);

        $response = $this->postJson('/api/tickets/actions/resolve-ticket', [
            'resources' => [$this->ticket->id],
            'fields' => [
                ['name' => 'resolution_note', 'value' => 'Bourrage papier retiré avec succès.']
            ],
        ]);

        $response->assertOk()
            ->assertJson(['data' => ['impacted' => 1]]);

        $this->ticket->refresh();

        $statusValue = is_object($this->ticket->status) ? $this->ticket->status->value : $this->ticket->status;
        $this->assertSame('resolved', $statusValue);
        $this->assertNotNull($this->ticket->resolved_at);

        $this->assertDatabaseHas('comments', [
            'ticket_id' => $this->ticket->id,
            'author_id' => $this->technician->id,
        ]);
    }

    public function test_un_utilisateur_non_autorise_ne_peut_pas_resoudre_un_ticket(): void
    {
        Sanctum::actingAs($this->intruder);

        $response = $this->postJson('/api/tickets/actions/resolve-ticket', [
            'resources' => [$this->ticket->id],
            'fields' => [
                ['name' => 'resolution_note', 'value' => 'Tentative non permise.']
            ],
        ]);

        $response->assertForbidden();
    }

    public function test_le_demandeur_peut_rouvrir_un_ticket_resolu(): void
    {
        $this->ticket->update([
            'status' => 'resolved',
            'resolved_at' => now(),
        ]);

        Sanctum::actingAs($this->requester);

        $response = $this->postJson('/api/tickets/actions/reopen-ticket', [
            'resources' => [$this->ticket->id],
            'fields' => [
                ['name' => 'reason', 'value' => 'Le voyant rouge clignote à nouveau.']
            ],
        ]);

        $response->assertOk()
            ->assertJson(['data' => ['impacted' => 1]]);

        $this->ticket->refresh();

        $statusValue = is_object($this->ticket->status) ? $this->ticket->status->value : $this->ticket->status;
        $this->assertSame('open', $statusValue);
        $this->assertNull($this->ticket->resolved_at);
    }

    public function test_cloture_definitive_dun_ticket_resolu(): void
    {
        $this->ticket->update([
            'status' => 'resolved',
            'resolved_at' => now(),
        ]);

        Sanctum::actingAs($this->requester);

        $response = $this->postJson('/api/tickets/actions/close-ticket', [
            'resources' => [$this->ticket->id],
            'fields' => [],
        ]);

        $response->assertOk()
            ->assertJson(['data' => ['impacted' => 1]]);

        $this->ticket->refresh();

        $statusValue = is_object($this->ticket->status) ? $this->ticket->status->value : $this->ticket->status;
        $this->assertSame('closed', $statusValue);
    }
}
