<?php

namespace Tickets\Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Tickets\Models\Comment;
use Tickets\Models\Ticket;

class TicketDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $technician = User::firstOrCreate(
            ['email' => 'tech@support.local'],
            ['name' => 'Technicien Support', 'password' => bcrypt('password')]
        );

        $requester = User::firstOrCreate(
            ['email' => 'client@support.local'],
            ['name' => 'Demandeur Client', 'password' => bcrypt('password')]
        );

        // 1. Ticket ouvert haute priorité
        $ticketOpen = Ticket::create([
            'requester_id' => $requester->id,
            'technician_id' => $technician->id,
            'title' => 'Panne imprimante réseau',
            'description' => 'Impossible d\'imprimer depuis le bureau comptabilité.',
            'status' => 'assigned',
            'priority' => 'high',
        ]);

        Comment::create([
            'ticket_id' => $ticketOpen->id,
            'author_id' => $technician->id,
            'body' => 'Prise en charge du ticket, vérification des baux DHCP en cours.',
        ]);

        // 2. Ticket critique en dépassement de SLA (pour tester la commande)
        $ticketBreached = Ticket::create([
            'requester_id' => $requester->id,
            'technician_id' => $technician->id,
            'title' => 'Serveur VPN inaccessible',
            'description' => 'Connexion impossible pour tous les télétravailleurs.',
            'status' => 'open',
            'priority' => 'critical',
        ]);
        $ticketBreached->timestamps = false;
        $ticketBreached->created_at = now()->subHours(8);
        $ticketBreached->save();

        // 3. Ticket résolu
        $ticketResolved = Ticket::create([
            'requester_id' => $requester->id,
            'technician_id' => $technician->id,
            'title' => 'Mise à jour suite bureautique',
            'description' => 'Besoin de la dernière version du logiciel.',
            'status' => 'resolved',
            'priority' => 'low',
            'resolved_at' => now()->subMinutes(30),
        ]);

        Comment::create([
            'ticket_id' => $ticketResolved->id,
            'author_id' => $technician->id,
            'body' => '[Résolution] Déploiement silencieux effectué avec succès.',
        ]);
    }
}
