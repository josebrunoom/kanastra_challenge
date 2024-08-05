<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Boletos;
use Illuminate\Support\Str;

class BoletosFactory extends Factory
{
    protected $model = Boletos::class;

    public function definition()
    {
        return [
            'name' => $this->faker->name,
            'governmentId' => $this->faker->numerify('###########'),
            'email' => $this->faker->unique()->safeEmail,
            'debtAmount' => $this->faker->randomFloat(2, 100, 1000),
            'debtDueDate' => $this->faker->date(),
            'debtId' => (string) Str::uuid(),
            'status' => 0, // 0 para não gerado, 1 para gerado
        ];
    }
}
