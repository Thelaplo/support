<?php

namespace Tests\Feature;

use App\Livewire\Tickets\TicketForm;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use Tickets\Models\Ticket;

class LivewireTicketFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_creation_dun_ticket_depuis_le_formulaire(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(TicketForm::class)
            ->set('title', 'Problème de connexion VPN')
            ->set('description', 'Erreur de négociation certificat TLS.')
            ->set('priority', 'high')
            ->call('save')
            ->assertHasNoErrors()
            ->assertSee(__('tickets.saved_success'));

        $this->assertDatabaseHas('tickets', [
            'requester_id' => $user->id,
            'title' => 'Problème de connexion VPN',
            'priority' => 'high',
        ]);
    }

    public function test_la_validation_refuse_une_priorite_hors_enum(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(TicketForm::class)
            ->set('title', 'Titre valide')
            ->set('description', 'Description valide')
            ->set('priority', 'invalid_priority')
            ->call('save')
            ->assertHasErrors(['priority']);
    }

    public function test_modification_dun_ticket_existant(): void
    {
        $user = User::factory()->create();
        $technician = User::factory()->create();

        $ticket = Ticket::create([
            'requester_id' => $user->id,
            'technician_id' => $technician->id,
            'title' => 'Ancien titre',
            'description' => 'Ancienne desc',
            'status' => 'open',
            'priority' => 'low',
        ]);

        Livewire::actingAs($user)
            ->test(TicketForm::class, ['ticket' => $ticket])
            ->set('title', 'Nouveau titre modifié')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame('Nouveau titre modifié', $ticket->fresh()->title);
    }

    public function test_transition_legale_assignation_au_technicien_connecte(): void
    {
        $requester = User::factory()->create();
        $technician = User::factory()->create();

        $ticket = Ticket::create([
            'requester_id' => $requester->id,
            'technician_id' => $requester->id,
            'title' => 'Ticket à assigner',
            'description' => 'Desc',
            'status' => 'open',
            'priority' => 'high',
        ]);

        Livewire::actingAs($technician)
            ->test(TicketForm::class, ['ticket' => $ticket])
            ->call('assignToMe')
            ->assertHasNoErrors()
            ->assertSee(__('tickets.assigned_success'));

        $this->assertSame($technician->id, $ticket->fresh()->technician_id);
        $statusValue = is_object($ticket->fresh()->status) ? $ticket->fresh()->status->value : $ticket->fresh()->status;
        $this->assertSame('assigned', $statusValue);
    }
}
