<?php

use App\Http\Controllers\Auth\LoginController;
use App\Livewire\Tickets\TicketList;
use App\Livewire\Tickets\TicketForm;
use Illuminate\Support\Facades\Route;

// Routes d'authentification publiques
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Redirection racine vers la liste ou le login
Route::get('/', function () {
    return auth()->check() ? redirect()->route('tickets.index') : redirect()->route('login');
});

// Routes protégées par l'authentification
Route::middleware(['auth'])->group(function () {
    Route::get('/tickets', TicketList::class)->name('tickets.index');
    Route::get('/tickets/create', TicketForm::class)->name('tickets.create');
    Route::get('/tickets/{ticket}/edit', TicketForm::class)->name('tickets.edit');
});
