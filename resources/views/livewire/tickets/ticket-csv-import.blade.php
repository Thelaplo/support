<div class="p-6 bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700">
    <h2 class="text-xl font-bold mb-4 text-zinc-900 dark:text-zinc-100">Import de tickets en masse (CSV)</h2>

    <form wire:submit="import" class="space-y-4">
        <div>
            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                Fichier CSV
            </label>
            <input type="file" wire:model="file" accept=".csv" class="block w-full text-sm text-zinc-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" />
            @error('file') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
        </div>

        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm font-medium hover:bg-blue-700 transition" wire:loading.attr="disabled">
            <span wire:loading.remove>Importer le fichier</span>
            <span wire:loading>Traitement en cours...</span>
        </button>
    </form>

    @if ($hasRun)
        <div class="mt-6 pt-4 border-t border-zinc-200 dark:border-zinc-700">
            <p class="text-sm font-semibold text-green-600 dark:text-green-400">
                Tickets importés avec succès : {{ $importedCount }}
            </p>

            @if (!empty($errorsList))
                <div class="mt-3 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-md">
                    <h3 class="text-sm font-semibold text-red-800 dark:text-red-300 mb-2">
                        Erreurs rencontrées ({{ count($errorsList) }} ligne(s)) :
                    </h3>
                    <ul class="list-disc list-inside text-xs text-red-700 dark:text-red-400 space-y-1">
                        @foreach ($errorsList as $line => $errs)
                            <li>
                                <strong>Ligne {{ $line }} :</strong>
                                @if (is_array($errs))
                                    {{ implode(', ', array_map(fn($e) => is_array($e) ? implode(' ', $e) : $e, $errs)) }}
                                @else
                                    {{ $errs }}
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    @endif
</div>
