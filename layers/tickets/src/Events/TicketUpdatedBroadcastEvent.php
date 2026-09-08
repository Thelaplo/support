<?php

namespace Tickets\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Tickets\Models\Ticket;

class TicketUpdatedBroadcastEvent implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    public function __construct(public Ticket $ticket)
    {
    }

    /**
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        $channels = [
            new PrivateChannel('tickets.dashboard'),
            new PrivateChannel('users.' . $this->ticket->requester_id),
        ];

        if ($this->ticket->technician_id) {
            $channels[] = new PrivateChannel('users.' . $this->ticket->technician_id);
        }

        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'ticket.updated';
    }
}
