<?php

namespace Tests\Unit;

use Tests\TestCase;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class UploadCsvTest extends TestCase
{
    public function test_upload_csv_validation_error()
    {
        // Simula um request com um arquivo inválido
        $file = UploadedFile::fake()->create('test.doc', 100); // Arquivo não é CSV
        $response = $this->json('POST', '/api/upload_csv', [
            'file' => $file,
        ]);

        $response->assertStatus(400)
            ->assertJsonStructure(['error']);
    }

    public function test_upload_csv_success()
    {
        // Simula um CSV válido
        $csvData = "name,governmentId,email,debtAmount,debtDueDate,debtId\n";
        $csvData .= "John Doe,123456789,johndoe@example.com,1000,2023-09-10,123e4567-e89b-12d3-a456-426614174000\n";

        Storage::fake('local');
        Storage::disk('local')->put('test.csv', $csvData);

        $file = new UploadedFile(Storage::disk('local')->path('test.csv'), 'test.csv', 'text/csv', null, true);

        $response = $this->json('POST', '/api/upload_csv', [
            'file' => $file,
        ]);
        
        $response->assertStatus(200)
            ->assertJson(['success' => 'Arquivo importado com sucesso!']);

        $this->assertDatabaseHas('boletos', [
            'email' => 'johndoe@example.com',
            'name' => 'John Doe',
        ]);

    }
}
