<?php

namespace Tickets\Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Tickets\Enums\TicketPriority;
use Tickets\Enums\TicketStatus;
use Tickets\Models\Comment;
use Tickets\Models\Ticket;

class TicketSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::factory()->count(10)->create();

        foreach (TicketStatus::cases() as $status) {
            foreach (TicketPriority::cases() as $priority) {
                $ticket = Ticket::factory()->create([
                    'requester_id' => $users->random()->id,
                    'technician_id' => $status === TicketStatus::Open ? null : $users->random()->id,
                    'status' => $status,
                    'priority' => $priority,
                    'resolved_at' => in_array($status, [TicketStatus::Resolved, TicketStatus::Closed], true) ? now() : null,
                ]);

                Comment::factory()->count(2)->create([
                    'ticket_id' => $ticket->id,
                    'author_id' => $users->random()->id,
                ]);
            }
        }
    }
}