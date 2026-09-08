<?php

namespace Tickets\Rest\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Tickets\Models\Ticket;

class TicketPdfController extends Controller
{
    public function download(Ticket $ticket): Response
    {
        $userId = auth()->id() ?? auth('sanctum')->id();

        if ($ticket->requester_id !== $userId && $ticket->technician_id !== $userId) {
            throw new AccessDeniedHttpException("Vous n'êtes pas autorisé à exporter ce ticket.");
        }

        $ticket->load(['requester', 'technician', 'comments.author']);

        $pdf = Pdf::loadView('tickets.intervention-report', [
            'ticket' => $ticket,
        ]);

        return $pdf->download(sprintf('rapport-intervention-ticket-%d.pdf', $ticket->id));
    }
}
