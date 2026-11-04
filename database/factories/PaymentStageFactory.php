<?php

namespace Database\Factories;

use App\Models\PaymentStage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PaymentStage>
 */
class PaymentStageFactory extends Factory
{
    protected $model = PaymentStage::class;

    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['Proses ACC', 'Proses Finance', 'Posted']),
            'sort_order' => 1,
            'is_final' => false,
            'is_active' => true,
        ];
    }
}

