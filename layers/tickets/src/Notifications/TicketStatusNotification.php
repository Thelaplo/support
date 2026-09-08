<?php

namespace Tickets\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Tickets\Models\Ticket;

class TicketStatusNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Ticket $ticket,
        public string $newStatus
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(sprintf('Mise à jour du ticket #%d : %s', $this->ticket->id, ucfirst($this->newStatus)))
            ->greeting(sprintf('Bonjour %s,', $notifiable->name ?? ''))
            ->line(sprintf('Le statut de votre ticket "%s" est désormais : %s.', $this->ticket->title, ucfirst($this->newStatus)))
            ->line('Merci de consulter l\'application de support pour plus de détails.');
    }
}
