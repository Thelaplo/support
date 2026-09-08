<?php

namespace Tests\Feature;

use Tests\TestCase;

class ApiDocumentationTest extends TestCase
{
    public function test_l_interface_de_documentation_api_est_accessible(): void
    {
        $response = $this->get('/docs/api');

        $response->assertOk();
    }

    public function test_le_schema_openapi_json_est_genere(): void
    {
        $response = $this->get('/docs/api.json');

        $response->assertOk()
            ->assertJsonPath('openapi', '3.1.0');
    }
}
