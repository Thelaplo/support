<?php

namespace Tests\Feature;

use App\Mcp\Tools\GetTicketDetailTool;
use App\Mcp\Tools\ListTicketsTool;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Mcp\Request;
use Tests\TestCase;
use Tickets\Enums\TicketPriority;
use Tickets\Enums\TicketStatus;
use Tickets\Models\Ticket;

class McpTicketToolsTest extends TestCase
{
    use RefreshDatabase;

    public function test_list_tickets_tool_retourne_les_tickets_au_format_json(): void
    {
        $user = User::factory()->create();

        Ticket::create([
            'title' => 'Serveur VPN inaccessible',
            'description' => 'Impossible de joindre le gateway',
            'priority' => TicketPriority::High,
            'status' => TicketStatus::Open,
            'requester_id' => $user->id,
        ]);

        $tool = new ListTicketsTool();
        $response = $tool->handle(new Request(['status' => 'open']));
        $content = (string) $response->content();

        $this->assertStringContainsString('Serveur VPN inaccessible', $content);
        $this->assertStringContainsString('"total": 1', $content);
    }

    public function test_get_ticket_detail_tool_retourne_les_details_complets(): void
    {
        $user = User::factory()->create(['name' => 'Alice Martin']);

        $ticket = Ticket::create([
            'title' => 'Imprimante reseau en panne',
            'description' => 'Code erreur 50.4',
            'priority' => TicketPriority::Normal,
            'status' => TicketStatus::Assigned,
            'requester_id' => $user->id,
        ]);

        $tool = new GetTicketDetailTool();
        $response = $tool->handle(new Request(['ticket_id' => $ticket->id]));
        $content = (string) $response->content();

        $this->assertStringContainsString('Imprimante reseau en panne', $content);
        $this->assertStringContainsString('Alice Martin', $content);
    }
}
