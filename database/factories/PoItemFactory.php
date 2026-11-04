<?php

namespace Database\Factories;

use App\Models\Item;
use App\Models\PoItem;
use App\Models\PurchaseOrder;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PoItem>
 */
class PoItemFactory extends Factory
{
    protected $model = PoItem::class;

    public function definition(): array
    {
        return [
            'purchase_order_id' => PurchaseOrder::factory(),
            'item_id' => Item::factory(),
            'item_name' => fake()->words(2, true),
            'unit' => 'pc',
            'qty_ordered' => fake()->randomFloat(2, 10, 500),
            'unit_price' => fake()->numberBetween(1500, 50000),
        ];
    }
}

