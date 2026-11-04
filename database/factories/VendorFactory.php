<?php

namespace Database\Factories;

use App\Models\Vendor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Vendor>
 */
class VendorFactory extends Factory
{
    protected $model = Vendor::class;

    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'contact' => fake()->phoneNumber(),
            'address' => fake()->address(),
            'is_active' => true,
        ];
    }
}

