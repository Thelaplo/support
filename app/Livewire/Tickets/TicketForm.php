<?php

namespace App\Livewire\Tickets;

use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Tickets\Enums\TicketPriority;
use Tickets\Events\TicketUpdatedBroadcastEvent;
use Tickets\Models\Ticket;
use Tickets\Rest\Actions\AssignTicketAction;

class TicketForm extends Component
{
    use WithFileUploads;

    public ?Ticket $ticket = null;

    public string $title = '';
    public string $description = '';
    public string $priority = 'normal';

    /** @var mixed */
    public $attachment = null;

    public function mount(?Ticket $ticket = null): void
    {
        if ($ticket && $ticket->exists) {
            $this->ticket = $ticket;
            $this->title = $ticket->title;
            $this->description = $ticket->description ?? '';
            $this->priority = is_object($ticket->priority) ? $ticket->priority->value : (string) $ticket->priority;
        }
    }

    protected function rules(): array
    {
        return [
            'title' => ['required', 'string', 'min:3', 'max:255'],
            'description' => ['required', 'string', 'min:5'],
            'priority' => ['required', Rule::enum(TicketPriority::class)],
            'attachment' => ['nullable', 'file', 'max:5120'],
        ];
    }

    public function save(): void
    {
        $validated = $this->validate();

        if ($this->ticket && $this->ticket->exists) {
            $this->ticket->update([
                'title' => $validated['title'],
                'description' => $validated['description'],
                'priority' => $validated['priority'],
            ]);
        } else {
            $this->ticket = Ticket::create([
                'requester_id' => auth()->id(),
                'title' => $validated['title'],
                'description' => $validated['description'],
                'priority' => $validated['priority'],
                'status' => 'open',
            ]);
        }

        if ($this->attachment) {
            $path = $this->attachment->store('attachments', 'public');
            $this->ticket->attachments()->create([
                'filename' => $this->attachment->getClientOriginalName(),
                'file_path' => $path,
                'file_size' => $this->attachment->getSize(),
                'mime_type' => $this->attachment->getMimeType() ?? 'application/octet-stream',
            ]);
            $this->reset('attachment');
        }

        // Diffusion temps réel E5
        TicketUpdatedBroadcastEvent::dispatch($this->ticket);

        session()->flash('success', __('tickets.saved_success'));
    }

    public function assignToMe(): void
    {
        if (!$this->ticket || !$this->ticket->exists) {
            return;
        }

        try {
            $action = new AssignTicketAction();
            $action->handle(['technician_id' => auth()->id()], collect([$this->ticket]));

            $this->ticket->refresh();

            // Diffusion temps réel E5
            TicketUpdatedBroadcastEvent::dispatch($this->ticket);

            session()->flash('success', __('tickets.assigned_success'));
        } catch (AccessDeniedHttpException $e) {
            $this->addError('transition', __('tickets.errors.transition_forbidden'));
        }
    }

    public function render(): View
    {
        return view('livewire.tickets.ticket-form');
    }
}
