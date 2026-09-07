<?php

namespace Tickets\Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Tickets\Enums\TicketPriority;
use Tickets\Enums\TicketStatus;
use Tickets\Models\Ticket;
use function faker;

class TicketFactory extends Factory
{
    protected $model = Ticket::class;

    public function definition(): array
    {
        return [
            'requester_id' => User::factory(),
            'technician_id' => null,
            'title' => faker()->sentences(1),
            'description' => faker()->paragraphs(2),
            'status' => faker()->randomElement(TicketStatus::cases()),
            'priority' => faker()->randomElement(TicketPriority::cases()),
            'resolved_at' => null,
        ];
    }

    public function assigned(?User $technician = null): static
    {
        return $this->state(fn (array $attributes) => [
            'technician_id' => $technician?->id ?? User::factory(),
            'status' => TicketStatus::Assigned,
        ]);
    }
}