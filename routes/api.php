<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Lomkit\Rest\Facades\Rest;
use Tickets\Rest\Controllers\CommentsController;
use Tickets\Rest\Controllers\TicketAttachmentController;
use Tickets\Rest\Controllers\TicketPdfController;
use Tickets\Rest\Controllers\TicketsController;
use Tickets\Rest\Controllers\TicketStatsController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function () {
    Rest::resource('tickets', TicketsController::class);
    Rest::resource('comments', CommentsController::class);

    Route::get('tickets-stats', [TicketStatsController::class, 'index']);
    Route::post('tickets/{ticket}/attachments', [TicketAttachmentController::class, 'store']);
    Route::get('tickets/{ticket}/export-pdf', [TicketPdfController::class, 'download']);
});
