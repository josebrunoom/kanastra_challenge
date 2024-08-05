<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Boletos;

class GenerateEmailTest extends TestCase
{

    use RefreshDatabase;

    public function test_send_email_integration_missing_debtId()
    {
        $response = $this->json('POST', '/api/generate_email', []);

        $response->assertStatus(404)
        ->assertExactJson([
            'Parametro não encontrado.'
         ]);
    }

    public function test_generate_email_integration_not_found()
    {
        $response = $this->json('POST', '/api/generate_email', [
            'debtId' => '123e4567-e89b-12d3-a456-426614174000',
        ]);

        $response->assertStatus(404)
        ->assertExactJson([
            'Boleto não encontrado.'
         ]);
    }

    public function test_generate_email_integration_already_generated()
    {
        $boleto = Boletos::factory()->create([
            'debtId' => '123e4567-e89b-12d3-a456-426614174000',
            'status' => 2, // Indica que o boleto já foi gerado
        ]);

        $response = $this->json('POST', '/api/generate_email', [
            'debtId' => $boleto->debtId,
        ]);

        $response->assertStatus(404)
            ->assertExactJson([
            'Email já foi enviado para ' . $boleto->name
         ]);
    }

    public function test_generate_email_integration_success()
    {
        $boleto = Boletos::factory()->create([
            'debtId' => '123e4567-e89b-12d3-a456-426614174000',
            'status' => 1, // Indica que o boleto ainda não foi enviado
        ]);

        $response = $this->json('POST', '/api/generate_email', [
            'debtId' => $boleto->debtId,
        ]);

        $response->assertStatus(200)
            ->assertExactJson([
                'Email enviado com sucesso para ' . $boleto->name
             ]);

        // Verifica se o status foi atualizado para 2 (email enviado)
        $this->assertDatabaseHas('boletos', [
            'debtId' => $boleto->debtId,
            'status' => 2,
        ]);
    }
}
