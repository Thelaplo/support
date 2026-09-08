<?php

namespace App\Livewire\Tickets;

use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithPagination;
use Tickets\Models\Ticket;

class TicketList extends Component
{
    use WithPagination;

    public string $status = '';
    public string $priority = '';
    public string $sortField = 'created_at';
    public string $sortDirection = 'desc';

    protected array $allowedSortFields = [
        'title',
        'status',
        'priority',
        'created_at',
    ];

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function updatingPriority(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if (!in_array($field, $this->allowedSortFields, true)) {
            return;
        }

        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
    }

    public function render(): View
    {
        $user = auth()->user();

        $query = Ticket::query()
            ->with(['requester', 'technician'])
            ->withCount('comments');

        if ($user) {
            $query->where(function ($q) use ($user) {
                $q->where('requester_id', $user->id)
                  ->orWhere('technician_id', $user->id);
            });
        }

        if ($this->status !== '') {
            $query->where('status', $this->status);
        }

        if ($this->priority !== '') {
            $query->where('priority', $this->priority);
        }

        $tickets = $query->orderBy($this->sortField, $this->sortDirection)
            ->paginate(25);

        return view('livewire.tickets.ticket-list', [
            'tickets' => $tickets,
        ]);
    }
}
