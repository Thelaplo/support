<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use Tickets\Models\Ticket;
use Tickets\Notifications\TicketStatusNotification;

class TicketNotificationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_le_demandeur_recoit_une_notification_a_la_resolution(): void
    {
        Notification::fake();

        $requester = User::factory()->create();
        $technician = User::factory()->create();

        $ticket = Ticket::create([
            'requester_id' => $requester->id,
            'technician_id' => $technician->id,
            'title' => 'Écran noir',
            'description' => 'Pas de signal vidéo.',
            'status' => 'assigned',
            'priority' => 'high',
        ]);

        Sanctum::actingAs($technician);

        $this->postJson('/api/tickets/actions/resolve-ticket', [
            'resources' => [$ticket->id],
            'fields' => [
                ['name' => 'resolution_note', 'value' => 'Câble HDMI rebranché.']
            ],
        ])->assertOk();

        Notification::assertSentTo(
            $requester,
            TicketStatusNotification::class,
            function ($notification) use ($ticket) {
                return $notification->ticket->id === $ticket->id && $notification->newStatus === 'resolved';
            }
        );
    }
}
