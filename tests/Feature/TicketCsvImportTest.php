<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tickets\Actions\ImportTicketsCsvAction;

class TicketCsvImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_import_csv_cree_les_tickets_valides_et_rapporte_les_erreurs(): void
    {
        $user = User::factory()->create();

        $csvContent = "title,description,priority\n";
        $csvContent .= "Panne ecran,Ecran noir au poste 4,high\n";
        $csvContent .= "Titre valide,Description ok,invalid_priority\n";
        $csvContent .= "Clavier HS,Touche espace bloquee,low\n";

        $tempFile = tempnam(sys_get_temp_dir(), 'csv_test_');
        file_put_contents($tempFile, $csvContent);

        $action = new ImportTicketsCsvAction();
        $result = $action->execute($tempFile, $user);

        unlink($tempFile);

        $this->assertSame(2, $result['imported']);
        $this->assertCount(1, $result['errors']);
        $this->assertArrayHasKey(3, $result['errors']);

        $this->assertDatabaseHas('tickets', [
            'title' => 'Panne ecran',
            'priority' => 'high',
        ]);

        $this->assertDatabaseHas('tickets', [
            'title' => 'Clavier HS',
            'priority' => 'low',
        ]);

        $this->assertDatabaseMissing('tickets', [
            'title' => 'Titre valide',
        ]);
    }
}
