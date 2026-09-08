<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use Tickets\Models\Ticket;

class TicketAttachmentsTest extends TestCase
{
    use RefreshDatabase;

    protected User $requester;
    protected User $technician;
    protected User $intruder;
    protected Ticket $ticket;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');

        $this->requester = User::factory()->create();
        $this->technician = User::factory()->create();
        $this->intruder = User::factory()->create();

        $this->ticket = Ticket::create([
            'requester_id' => $this->requester->id,
            'technician_id' => $this->technician->id,
            'title' => 'Erreur crash système',
            'description' => 'Log d erreur joint.',
            'status' => 'assigned',
            'priority' => 'high',
        ]);
    }

    public function test_le_demandeur_peut_uploader_un_fichier_sur_son_ticket(): void
    {
        Sanctum::actingAs($this->requester);

        $file = UploadedFile::fake()->create('error.log', 120, 'text/plain');

        $response = $this->postJson("/api/tickets/{$this->ticket->id}/attachments", [
            'file' => $file,
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.filename', 'error.log');

        $this->assertDatabaseHas('ticket_attachments', [
            'ticket_id' => $this->ticket->id,
            'user_id' => $this->requester->id,
            'filename' => 'error.log',
        ]);

        $path = $response->json('data.path');
        Storage::disk('local')->assertExists($path);
    }

    public function test_un_intrus_ne_peut_pas_uploader_de_fichier_sur_un_ticket_tiers(): void
    {
        Sanctum::actingAs($this->intruder);

        $file = UploadedFile::fake()->image('screenshot.png');

        $response = $this->postJson("/api/tickets/{$this->ticket->id}/attachments", [
            'file' => $file,
        ]);

        $response->assertForbidden();
    }
}
