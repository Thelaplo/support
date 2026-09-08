<?php

declare(strict_types=1);

namespace Tickets\Tests\Feature;

use Tests\TestCase;
use Tickets\Models\Ticket;
use Tickets\Enums\TicketPriority;
use Tickets\Notifications\Strategies\CriticalNotificationStrategy;
use Tickets\Notifications\Strategies\StandardNotificationStrategy;
use Tickets\Jobs\ImportTicketsJob;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TicketExtensionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_resolves_correct_notification_strategy(): void
    {
        $this->assertInstanceOf(StandardNotificationStrategy::class, TicketPriority::Low->resolveNotificationStrategy());
        $this->assertInstanceOf(CriticalNotificationStrategy::class, TicketPriority::Critical->resolveNotificationStrategy());
    }

    public function test_it_can_import_tickets_from_csv(): void
    {
        User::factory()->create([
            "email" => "test.requester@example.com"
        ]);

        $csvPath = storage_path("app/test_tickets.csv");
        $csvContent = "title,description,priority,requester_email\n";
        $csvContent .= "Test Ticket 1,Description test,normal,test.requester@example.com\n";
        file_put_contents($csvPath, $csvContent);

        (new ImportTicketsJob($csvPath))->handle();

        $this->assertDatabaseHas("tickets", [
            "title" => "Test Ticket 1",
            "priority" => "normal",
        ]);

        if (file_exists($csvPath)) {
            unlink($csvPath);
        }
    }
}
