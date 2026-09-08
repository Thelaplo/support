<?php

declare(strict_types=1);

namespace Tickets\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Tickets\Models\Ticket;

class TicketEscalatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Ticket $ticket)
    {
    }

    public function via(mixed $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(mixed $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("[ESCALADE] Ticket en retard : {$this->ticket->title}")
            ->line("Le ticket #{$this->ticket->id} a dépassé son délai cible de traitement et a fait l'objet d'une escalade.")
            ->line("Priorité actuelle : {$this->ticket->priority->value}")
            ->action('Consulter le ticket', url("/tickets/{$this->ticket->id}/edit"))
            ->line('Veuillez traiter cette demande dans les plus brefs délais.');
    }
}
