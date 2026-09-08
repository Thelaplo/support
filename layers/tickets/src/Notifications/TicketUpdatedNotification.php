<?php

namespace Tickets\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Tickets\Models\Ticket;

class TicketUpdatedNotification extends Notification
{
    use Queueable;

    public function __construct(public Ticket $ticket)
    {
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'ticket_id' => $this->ticket->id,
            'title' => $this->ticket->title,
            'message' => 'Le ticket a été mis à jour.',
        ];
    }
}
