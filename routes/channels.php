<?php

use App\Models\User;
use Illuminate\Support\Facades\Broadcast;
use Tickets\Models\Ticket;

Broadcast::channel('users.{id}', function (User $user, int $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('tickets.dashboard', function (User $user) {
    return $user->can('tickets.view-any') || $user->hasRole('technician') || $user->hasRole('manager');
});

Broadcast::channel('tickets.{ticketId}', function (User $user, int $ticketId) {
    $ticket = Ticket::find($ticketId);
    if (! $ticket) {
        return false;
    }

    return (int) $user->id === (int) $ticket->requester_id
        || (int) $user->id === (int) $ticket->technician_id
        || $user->can('tickets.view-any');
});
