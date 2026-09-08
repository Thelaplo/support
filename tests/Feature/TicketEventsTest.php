<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use Tickets\Events\TicketStatusUpdated;
use Tickets\Models\Ticket;

class TicketEventsTest extends TestCase
{
    use RefreshDatabase;

    public function test_levenement_est_declenche_lors_de_la_resolution(): void
    {
        Event::fake([TicketStatusUpdated::class]);

        $technician = User::factory()->create();
        $ticket = Ticket::create([
            'requester_id' => $technician->id,
            'technician_id' => $technician->id,
            'title' => 'Panne réseau',
            'description' => 'Switch déconnecté.',
            'status' => 'assigned',
            'priority' => 'high',
        ]);

        Sanctum::actingAs($technician);

        $this->postJson('/api/tickets/actions/resolve-ticket', [
            'resources' => [$ticket->id],
            'fields' => [
                ['name' => 'resolution_note', 'value' => 'Câble réinséré et testé.']
            ],
        ])->assertOk();

        Event::assertDispatched(TicketStatusUpdated::class, function ($event) use ($ticket) {
            return $event->ticket->id === $ticket->id && $event->newStatus === 'resolved';
        });
    }

    public function test_levenement_est_declenche_lors_de_la_reouverture(): void
    {
        Event::fake([TicketStatusUpdated::class]);

        $requester = User::factory()->create();
        $ticket = Ticket::create([
            'requester_id' => $requester->id,
            'technician_id' => $requester->id,
            'title' => 'Panne réseau',
            'description' => 'Switch déconnecté.',
            'status' => 'resolved',
            'priority' => 'high',
            'resolved_at' => now(),
        ]);

        Sanctum::actingAs($requester);

        $this->postJson('/api/tickets/actions/reopen-ticket', [
            'resources' => [$ticket->id],
            'fields' => [
                ['name' => 'reason', 'value' => 'La liaison retombe.']
            ],
        ])->assertOk();

        Event::assertDispatched(TicketStatusUpdated::class, function ($event) use ($ticket) {
            return $event->ticket->id === $ticket->id && $event->newStatus === 'open';
        });
    }
}
