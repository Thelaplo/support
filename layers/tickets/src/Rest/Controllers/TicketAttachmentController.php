<?php

namespace Tickets\Rest\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Tickets\Models\Ticket;

class TicketAttachmentController extends Controller
{
    public function store(Request $request, Ticket $ticket): JsonResponse
    {
        $userId = auth()->id() ?? auth('sanctum')->id();

        if ($ticket->requester_id !== $userId && $ticket->technician_id !== $userId) {
            throw new AccessDeniedHttpException("Vous n'êtes pas autorisé à ajouter une pièce jointe à ce ticket.");
        }

        $request->validate([
            'file' => ['required', 'file', 'max:10240', 'mimes:jpg,jpeg,png,pdf,txt,log'],
        ]);

        $uploadedFile = $request->file('file');
        $path = $uploadedFile->store('attachments/' . $ticket->id, 'local');

        $attachment = $ticket->attachments()->create([
            'user_id' => $userId,
            'filename' => $uploadedFile->getClientOriginalName(),
            'path' => $path,
            'mime_type' => $uploadedFile->getClientMimeType(),
            'size' => $uploadedFile->getSize(),
        ]);

        return response()->json([
            'message' => 'Fichier téléversé avec succès.',
            'data' => $attachment,
        ], 201);
    }
}
