<div class="space-y-4">
    <div class="flex gap-4">
        <div>
            <label class="block text-sm font-medium">{{ __('tickets.status') }}</label>
            <select wire:model.live="status" class="border rounded p-2">
                <option value="">{{ __('tickets.all_statuses') }}</option>
                <option value="open">{{ __('tickets.statuses.open') }}</option>
                <option value="assigned">{{ __('tickets.statuses.assigned') }}</option>
                <option value="in_progress">{{ __('tickets.statuses.in_progress') }}</option>
                <option value="resolved">{{ __('tickets.statuses.resolved') }}</option>
                <option value="closed">{{ __('tickets.statuses.closed') }}</option>
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium">{{ __('tickets.priority') }}</label>
            <select wire:model.live="priority" class="border rounded p-2">
                <option value="">{{ __('tickets.all_priorities') }}</option>
                <option value="low">{{ __('tickets.priorities.low') }}</option>
                <option value="normal">{{ __('tickets.priorities.normal') }}</option>
                <option value="high">{{ __('tickets.priorities.high') }}</option>
                <option value="critical">{{ __('tickets.priorities.critical') }}</option>
            </select>
        </div>
    </div>

    <table class="w-full border-collapse border border-gray-200">
        <thead>
            <tr class="bg-gray-100">
                <th wire:click="sortBy('title')" class="cursor-pointer p-2 text-left border">
                    {{ __('tickets.title') }}
                </th>
                <th class="p-2 text-left border">{{ __('tickets.requester') }}</th>
                <th class="p-2 text-left border">{{ __('tickets.technician') }}</th>
                <th wire:click="sortBy('status')" class="cursor-pointer p-2 text-left border">
                    {{ __('tickets.status') }}
                </th>
                <th wire:click="sortBy('priority')" class="cursor-pointer p-2 text-left border">
                    {{ __('tickets.priority') }}
                </th>
                <th class="p-2 text-left border">{{ __('tickets.comments_count') }}</th>
                <th wire:click="sortBy('created_at')" class="cursor-pointer p-2 text-left border">
                    {{ __('tickets.created_at') }}
                </th>
            </tr>
        </thead>
        <tbody>
            @forelse($tickets as $ticket)
                <tr class="border-b hover:bg-gray-50">
                    <td class="p-2 border">{{ $ticket->title }}</td>
                    <td class="p-2 border">{{ $ticket->requester->name ?? '-' }}</td>
                    <td class="p-2 border">{{ $ticket->technician->name ?? __('tickets.unassigned') }}</td>
                    <td class="p-2 border">
                        {{ __('tickets.statuses.' . (is_object($ticket->status) ? $ticket->status->value : $ticket->status)) }}
                    </td>
                    <td class="p-2 border">
                        {{ __('tickets.priorities.' . (is_object($ticket->priority) ? $ticket->priority->value : $ticket->priority)) }}
                    </td>
                    <td class="p-2 border">{{ $ticket->comments_count }}</td>
                    <td class="p-2 border">{{ $ticket->created_at->format('d/m/Y H:i') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="p-4 text-center text-gray-500">
                        {{ __('tickets.empty') }}
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div>
        {{ $tickets->links() }}
    </div>
</div>
