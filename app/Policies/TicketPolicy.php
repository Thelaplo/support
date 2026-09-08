<?php

namespace App\Policies;

use App\Models\User;
use Tickets\Models\Ticket;

class TicketPolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, Ticket $ticket): bool
    {
        return true;
    }

    public function create(?User $user): bool
    {
        return $user !== null;
    }

    public function update(?User $user, Ticket $ticket): bool
    {
        if (! $user) {
            return false;
        }

        return $user->id === $ticket->requester_id
            || $user->id === $ticket->technician_id;
    }

    public function delete(?User $user, Ticket $ticket): bool
    {
        return $user && $user->id === $ticket->requester_id;
    }

    /**
     * Autorisation pour ResolveTicketAction
     */
    public function resolveTicket(?User $user, Ticket $ticket): bool
    {
        if (! $user) {
            return false;
        }

        return $user->id === $ticket->requester_id
            || $user->id === $ticket->technician_id;
    }

    /**
     * Autorisation pour AssignTicketAction
     */
    public function assignTicket(?User $user, Ticket $ticket): bool
    {
        return $user !== null;
    }
}
