<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Rapport d'intervention #{{ $ticket->id }}</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #333; line-height: 1.5; }
        .header { border-bottom: 2px solid #2563eb; padding-bottom: 10px; margin-bottom: 20px; }
        .title { font-size: 20px; font-weight: bold; color: #1e3a8a; }
        .badge { display: inline-block; padding: 2px 8px; font-weight: bold; text-transform: uppercase; font-size: 10px; border-radius: 4px; }
        .badge-critical { background-color: #fee2e2; color: #b91c1c; }
        .badge-high { background-color: #ffedd5; color: #c2410c; }
        .badge-normal { background-color: #f3f4f6; color: #374151; }
        .badge-resolved { background-color: #dcfce7; color: #15803d; }
        .table-info { width: 100%; margin-bottom: 20px; border-collapse: collapse; }
        .table-info td { padding: 6px; border: 1px solid #e5e7eb; }
        .table-info td.label { font-weight: bold; width: 25%; background-color: #f9fafb; }
        .comments { margin-top: 20px; }
        .comment-box { border-left: 3px solid #2563eb; padding-left: 10px; margin-bottom: 12px; }
        .comment-meta { font-size: 10px; color: #6b7280; margin-bottom: 4px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">FICHE D'INTERVENTION #{{ $ticket->id }}</div>
        <p>Généré le {{ now()->format('d/m/Y à H:i') }}</p>
    </div>

    <table class="table-info">
        <tr>
            <td class="label">Titre</td>
            <td colspan="3"><strong>{{ $ticket->title }}</strong></td>
        </tr>
        <tr>
            <td class="label">Statut</td>
            <td><span class="badge badge-resolved">{{ is_object($ticket->status) ? $ticket->status->value : $ticket->status }}</span></td>
            <td class="label">Priorité</td>
            <td><span class="badge badge-high">{{ is_object($ticket->priority) ? $ticket->priority->value : $ticket->priority }}</span></td>
        </tr>
        <tr>
            <td class="label">Demandeur</td>
            <td>{{ $ticket->requester->name ?? 'Inconnu' }} ({{ $ticket->requester->email ?? '-' }})</td>
            <td class="label">Technicien</td>
            <td>{{ $ticket->technician->name ?? 'Non assigné' }}</td>
        </tr>
        <tr>
            <td class="label">Créé le</td>
            <td>{{ $ticket->created_at->format('d/m/Y H:i') }}</td>
            <td class="label">Résolu le</td>
            <td>{{ $ticket->resolved_at ? $ticket->resolved_at->format('d/m/Y H:i') : 'En attente' }}</td>
        </tr>
        <tr>
            <td class="label">Description initiale</td>
            <td colspan="3">{{ $ticket->description }}</td>
        </tr>
    </table>

    <div class="comments">
        <h3>Historique des échanges ({{ $ticket->comments->count() }})</h3>
        @forelse($ticket->comments as $comment)
            <div class="comment-box">
                <div class="comment-meta">
                    Par <strong>{{ $comment->author->name ?? 'Système' }}</strong> le {{ $comment->created_at->format('d/m/Y H:i') }}
                </div>
                <div>{{ $comment->body }}</div>
            </div>
        @empty
            <p>Aucun commentaire enregistré.</p>
        @endforelse
    </div>
</body>
</html>
