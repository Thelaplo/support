<div class="max-w-xl mx-auto p-4 bg-white rounded shadow">
    @if (session()->has('success'))
        <div class="p-3 mb-4 text-green-700 bg-green-100 rounded">
            {{ session('success') }}
        </div>
    @endif

    @error('transition')
        <div class="p-3 mb-4 text-red-700 bg-red-100 rounded">
            {{ $message }}
        </div>
    @enderror

    <form wire:submit="save" class="space-y-4">
        <div>
            <label class="block text-sm font-medium">{{ __('tickets.title') }}</label>
            <input type="text" wire:model="title" class="w-full border rounded p-2 mt-1">
            @error('title') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium">{{ __('tickets.description') }}</label>
            <textarea wire:model="description" rows="4" class="w-full border rounded p-2 mt-1"></textarea>
            @error('description') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium">{{ __('tickets.priority') }}</label>
            <select wire:model="priority" class="w-full border rounded p-2 mt-1">
                <option value="low">{{ __('tickets.priorities.low') }}</option>
                <option value="normal">{{ __('tickets.priorities.normal') }}</option>
                <option value="high">{{ __('tickets.priorities.high') }}</option>
                <option value="critical">{{ __('tickets.priorities.critical') }}</option>
            </select>
            @error('priority') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium">{{ __('tickets.attachment') }}</label>
            <input type="file" wire:model="attachment" class="w-full border rounded p-2 mt-1">
            @error('attachment') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
        </div>

        <div class="flex items-center gap-3 pt-2">
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">
                {{ __('tickets.save') }}
            </button>

            @if($ticket && $ticket->exists)
                <button type="button" wire:click="assignToMe" class="px-4 py-2 bg-gray-600 text-white rounded">
                    {{ __('tickets.assign_to_me') }}
                </button>
            @endif
        </div>
    </form>
</div>
