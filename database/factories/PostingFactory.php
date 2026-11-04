<?php

namespace Database\Factories;

use App\Models\Posting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Posting>
 */
class PostingFactory extends Factory
{
    protected $model = Posting::class;

    public function definition(): array
    {
        return [
            'document_number' => '50000'.fake()->unique()->numerify('#####'),
            'posting_date' => fake()->date(),
            'total_paid' => fake()->numberBetween(100000, 5000000),
            'notes' => fake()->sentence(),
        ];
    }
}

