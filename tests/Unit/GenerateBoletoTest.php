<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Boletos;

class GenerateBoletoTest extends TestCase
{

    use RefreshDatabase;

    public function test_generate_boleto_integration_missing_debtId()
    {
        $response = $this->json('POST', '/api/generate_boleto', []);

        $response->assertStatus(404)
        ->assertExactJson([
            'Parametro não encontrado.'
         ]);
    }

    public function test_generate_boleto_integration_not_found()
    {
        $response = $this->json('POST', '/api/generate_boleto', [
            'debtId' => '123e4567-e89b-12d3-a456-426614174000',
        ]);

        $response->assertStatus(404)
        ->assertExactJson([
            'Boleto não encontrado.'
         ]);
    }

    public function test_generate_boleto_integration_already_generated()
    {
        $boleto = Boletos::factory()->create([
            'debtId' => '123e4567-e89b-12d3-a456-426614174000',
            'status' => 1, // Indica que o boleto já foi gerado
        ]);

        $response = $this->json('POST', '/api/generate_boleto', [
            'debtId' => $boleto->debtId,
        ]);

        $response->assertStatus(404)
            ->assertExactJson([
            'Boleto já foi gerado para ' . $boleto->name
         ]);
    }

    public function test_generate_boleto_integration_success()
    {
        $boleto = Boletos::factory()->create([
            'debtId' => '123e4567-e89b-12d3-a456-426614174000',
            'status' => 0, // Indica que o boleto ainda não foi gerado
        ]);

        $response = $this->json('POST', '/api/generate_boleto', [
            'debtId' => $boleto->debtId,
        ]);

        $response->assertStatus(200)
            ->assertExactJson([
                'Boleto gerado com sucesso para ' . $boleto->name
             ]);

        // Verifica se o status foi atualizado para 1 (boleto gerado)
        $this->assertDatabaseHas('boletos', [
            'debtId' => $boleto->debtId,
            'status' => 1,
        ]);
    }
}
