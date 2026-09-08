<?php

namespace App\Livewire\Tickets;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;
use Tickets\Actions\ImportTicketsCsvAction;

class TicketCsvImport extends Component
{
    use WithFileUploads;

    public $file;
    public int $importedCount = 0;
    public array $errorsList = [];
    public bool $hasRun = false;

    protected array $rules = [
        'file' => 'required|file|mimes:csv,txt|max:2048',
    ];

    public function import(ImportTicketsCsvAction $action): void
    {
        $this->validate();

        $path = $this->file->getRealPath();
        $user = Auth::user();

        $result = $action->execute($path, $user);

        $this->importedCount = $result['imported'];
        $this->errorsList = $result['errors'];
        $this->hasRun = true;

        $this->reset('file');
    }

    public function render()
    {
        return view('livewire.tickets.ticket-csv-import');
    }
}
