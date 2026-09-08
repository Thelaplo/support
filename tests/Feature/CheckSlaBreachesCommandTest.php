<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tickets\Models\Ticket;

class CheckSlaBreachesCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_la_commande_signale_aucun_depassement_si_tout_est_dans_les_delais(): void
    {
        $user = User::factory()->create();

        Ticket::create([
            'requester_id' => $user->id,
            'title' => 'Ticket récent',
            'description' => 'Créé récemment',
            'status' => 'open',
            'priority' => 'low',
        ]);

        $this->artisan('tickets:check-sla')
            ->expectsOutput('Aucun dépassement de SLA détecté.')
            ->assertSuccessful();
    }

    public function test_la_commande_detecte_et_affiche_les_tickets_hors_sla(): void
    {
        $user = User::factory()->create();

        $breachedTicket = Ticket::create([
            'requester_id' => $user->id,
            'title' => 'Panne bloquante ancienne',
            'description' => 'Urgent',
            'status' => 'open',
            'priority' => 'critical',
        ]);

        $breachedTicket->timestamps = false;
        $breachedTicket->created_at = now()->subHours(6);
        $breachedTicket->save();

        $this->artisan('tickets:check-sla')
            ->expectsOutput('1 ticket(s) en dépassement de SLA détecté(s) !')
            ->assertFailed();
    }
}
