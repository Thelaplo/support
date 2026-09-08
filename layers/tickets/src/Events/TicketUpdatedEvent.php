<?php

declare(strict_types=1);

namespace Tickets\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Tickets\Models\Ticket;

class TicketUpdatedEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Ticket $ticket)
    {
    }

    public function broadcastOn(): array
    {
        // Canal privé sécurisé pour la couche tickets
        return [
            new PrivateChannel("tickets." . $this->ticket->id),
            new PrivateChannel('users.' . $this->ticket->requester_id),
            new PrivateChannel("tickets-list"),
        ];
    }

    public function broadcastAs(): string
    {
        return "ticket.updated";
    }
}
