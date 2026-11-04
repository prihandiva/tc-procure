<?php

namespace Database\Factories;

use App\Models\PaymentRequest;
use App\Models\PaymentStage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PaymentRequest>
 */
class PaymentRequestFactory extends Factory
{
    protected $model = PaymentRequest::class;

    public function definition(): array
    {
        return [
            'number' => 'TC/PC/'.fake()->unique()->numerify('##-X/2026/###'),
            'current_stage_id' => PaymentStage::factory(),
            'submitted_at' => now(),
            'notes' => fake()->sentence(),
            'posting_id' => null,
        ];
    }
}

