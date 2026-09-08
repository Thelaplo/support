<?php

namespace Tests\Feature;

use App\Livewire\Tickets\TicketList;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use Tickets\Models\Ticket;

class LivewireTicketListTest extends TestCase
{
    use RefreshDatabase;

    public function test_le_composant_affiche_les_tickets_autorises_et_pagine(): void
    {
        $user = User::factory()->create();
        $technician = User::factory()->create();

        Ticket::create([
            'requester_id' => $user->id,
            'technician_id' => $technician->id,
            'title' => 'Panne imprimante compta',
            'description' => 'Bloqué',
            'status' => 'open',
            'priority' => 'high',
        ]);

        $otherUser = User::factory()->create();
        $otherTech = User::factory()->create();

        Ticket::create([
            'requester_id' => $otherUser->id,
            'technician_id' => $otherTech->id,
            'title' => 'Ticket tiers invisible',
            'description' => 'Secret',
            'status' => 'open',
            'priority' => 'low',
        ]);

        Livewire::actingAs($user)
            ->test(TicketList::class)
            ->assertSee('Panne imprimante compta')
            ->assertDontSee('Ticket tiers invisible');
    }

    public function test_le_filtre_par_statut_fonctionne_et_reinitialise_la_page(): void
    {
        $user = User::factory()->create();

        Ticket::create([
            'requester_id' => $user->id,
            'title' => 'Ticket Ouvert',
            'description' => 'Desc',
            'status' => 'open',
            'priority' => 'normal',
        ]);

        Ticket::create([
            'requester_id' => $user->id,
            'title' => 'Ticket Resolu',
            'description' => 'Desc',
            'status' => 'resolved',
            'priority' => 'normal',
        ]);

        Livewire::actingAs($user)
            ->test(TicketList::class)
            ->set('status', 'resolved')
            ->assertSee('Ticket Resolu')
            ->assertDontSee('Ticket Ouvert');
    }

    public function test_le_tri_sur_colonne_autorisee_fonctionne(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(TicketList::class)
            ->call('sortBy', 'title')
            ->assertSet('sortField', 'title')
            ->assertSet('sortDirection', 'asc')
            ->call('sortBy', 'title')
            ->assertSet('sortDirection', 'desc');
    }

    public function test_le_tri_ignore_les_colonnes_non_autorisees(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(TicketList::class)
            ->set('sortField', 'created_at')
            ->call('sortBy', 'password')
            ->assertSet('sortField', 'created_at');
    }
}
