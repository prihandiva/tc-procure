<?php

namespace Database\Factories;

use App\Enums\ItemType;
use App\Models\Item;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Item>
 */
class ItemFactory extends Factory
{
    protected $model = Item::class;

    public function definition(): array
    {
        return [
            'name' => fake()->words(2, true),
            'unit' => fake()->randomElement(['pc', 'box', 'pak', 'kg']),
            'type' => ItemType::GOODS,
            'default_price' => fake()->numberBetween(1000, 100000),
            'is_active' => true,
        ];
    }

    public function service(): static
    {
        return $this->state(fn () => [
            'type' => ItemType::SERVICE,
            'unit' => 'au',
        ]);
    }
}

