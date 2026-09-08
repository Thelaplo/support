<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

use App\Livewire\Tickets\TicketList;
use App\Livewire\Tickets\TicketForm;

Route::middleware(['auth'])->group(function () {
    Route::get('/tickets', TicketList::class)->name('tickets.index');
    Route::get('/tickets/create', TicketForm::class)->name('tickets.create');
    Route::get('/tickets/{ticket}/edit', TicketForm::class)->name('tickets.edit');
});

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('tickets.index');
    }
    // Auto-login du premier user pour tester directement si en local
    $user = \App\Models\User::first();
    if ($user) {
        auth()->login($user);
        return redirect()->route('tickets.index');
    }
    return redirect()->route('tickets.index');
});
