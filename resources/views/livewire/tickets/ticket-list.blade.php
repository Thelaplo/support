<div class="space-y-6">
    <!-- En-tête de section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Gestion des incidents & demandes</h1>
            <p class="text-sm text-slate-500 mt-1">Supervisez et traitez les tickets informatiques en temps réel.</p>
        </div>
    </div>

    <!-- Barre de filtres stylisée -->
    <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-200 flex flex-wrap gap-4 items-center">
        <div class="flex items-center gap-2">
            <span class="text-xs font-semibold uppercase text-slate-500 tracking-wider">Filtres :</span>
        </div>

        <div class="w-48">
            <select wire:model.live="status" class="w-full text-sm border-slate-300 rounded-lg shadow-sm focus:border-xefi-red focus:ring-xefi-red">
                <option value="">{{ __('tickets.all_statuses') }}</option>
                <option value="open">{{ __('tickets.statuses.open') }}</option>
                <option value="assigned">{{ __('tickets.statuses.assigned') }}</option>
                <option value="in_progress">{{ __('tickets.statuses.in_progress') }}</option>
                <option value="resolved">{{ __('tickets.statuses.resolved') }}</option>
                <option value="closed">{{ __('tickets.statuses.closed') }}</option>
            </select>
        </div>

        <div class="w-48">
            <select wire:model.live="priority" class="w-full text-sm border-slate-300 rounded-lg shadow-sm focus:border-xefi-red focus:ring-xefi-red">
                <option value="">{{ __('tickets.all_priorities') }}</option>
                <option value="low">{{ __('tickets.priorities.low') }}</option>
                <option value="normal">{{ __('tickets.priorities.normal') }}</option>
                <option value="high">{{ __('tickets.priorities.high') }}</option>
                <option value="critical">{{ __('tickets.priorities.critical') }}</option>
            </select>
        </div>
    </div>

    <!-- Tableau des tickets -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-700">
                <thead class="bg-slate-100/75 text-xs uppercase font-semibold text-slate-600 border-b border-slate-200">
                    <tr>
                        <th wire:click="sortBy('title')" class="cursor-pointer px-6 py-3.5 hover:text-xefi-red transition">
                            <div class="flex items-center gap-1.5">
                                {{ __('tickets.title') }}
                                <svg class="w-3.5 h-3.5 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/></svg>
                            </div>
                        </th>
                        <th class="px-6 py-3.5">{{ __('tickets.requester') }}</th>
                        <th class="px-6 py-3.5">{{ __('tickets.technician') }}</th>
                        <th wire:click="sortBy('status')" class="cursor-pointer px-6 py-3.5 hover:text-xefi-red transition">
                            {{ __('tickets.status') }}
                        </th>
                        <th wire:click="sortBy('priority')" class="cursor-pointer px-6 py-3.5 hover:text-xefi-red transition">
                            {{ __('tickets.priority') }}
                        </th>
                        <th class="px-6 py-3.5 text-center">{{ __('tickets.comments_count') }}</th>
                        <th wire:click="sortBy('created_at')" class="cursor-pointer px-6 py-3.5 hover:text-xefi-red transition">
                            {{ __('tickets.created_at') }}
                        </th>
                        <th class="px-6 py-3.5 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($tickets as $ticket)
                        @php
                            $st = is_object($ticket->status) ? $ticket->status->value : $ticket->status;
                            $pr = is_object($ticket->priority) ? $ticket->priority->value : $ticket->priority;
                        @endphp
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-6 py-4 font-medium text-slate-900">
                                <a href="/tickets/{{ $ticket->id }}/edit" class="hover:text-xefi-red transition">
                                    {{ $ticket->title }}
                                </a>
                            </td>
                            <td class="px-6 py-4 text-slate-600">
                                {{ $ticket->requester->name ?? '-' }}
                            </td>
                            <td class="px-6 py-4">
                                @if($ticket->technician)
                                    <span class="inline-flex items-center gap-1.5 text-slate-700 font-medium">
                                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                        {{ $ticket->technician->name }}
                                    </span>
                                @else
                                    <span class="text-slate-400 italic">{{ __('tickets.unassigned') }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if($st === 'open')
                                    <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">Ouvert</span>
                                @elseif($st === 'assigned')
                                    <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-amber-100 text-amber-800">Assigné</span>
                                @elseif($st === 'in_progress')
                                    <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-indigo-100 text-indigo-800">En cours</span>
                                @elseif($st === 'resolved')
                                    <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-emerald-100 text-emerald-800">Résolu</span>
                                @else
                                    <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-slate-100 text-slate-700">Clôturé</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if($pr === 'critical')
                                    <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-red-100 text-xefi-red">Critique</span>
                                @elseif($pr === 'high')
                                    <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-orange-100 text-orange-800">Haute</span>
                                @elseif($pr === 'normal')
                                    <span class="px-2.5 py-1 text-xs font-medium rounded-full bg-slate-100 text-slate-700">Normale</span>
                                @else
                                    <span class="px-2.5 py-1 text-xs font-medium rounded-full bg-slate-100 text-slate-500">Basse</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-700">
                                    {{ $ticket->comments_count }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-xs text-slate-500">
                                {{ $ticket->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="/tickets/{{ $ticket->id }}/edit" class="text-xs font-semibold text-xefi-red hover:text-xefi-darkred underline">
                                    Consulter
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-slate-500">
                                <div class="max-w-sm mx-auto space-y-2">
                                    <p class="font-medium text-slate-700">{{ __('tickets.empty') }}</p>
                                    <p class="text-xs text-slate-400">Aucun ticket ne correspond aux critères sélectionnés.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="px-6 py-4 border-t border-slate-200 bg-slate-50/50">
            {{ $tickets->links() }}
        </div>
    </div>
</div>
