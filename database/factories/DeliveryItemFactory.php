<?php

namespace Database\Factories;

use App\Models\Delivery;
use App\Models\DeliveryItem;
use App\Models\PoItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DeliveryItem>
 */
class DeliveryItemFactory extends Factory
{
    protected $model = DeliveryItem::class;

    public function definition(): array
    {
        return [
            'delivery_id' => Delivery::factory(),
            'po_item_id' => PoItem::factory(),
            'qty_sent' => 20.00,
            'qty_received' => 20.00,
            'training_name' => 'Training Basic Safety',
            'needed_date' => fake()->date(),
            'variant_note' => '5 lemper, 5 pastel, 5 bolu, 5 risoles',
            'is_invoiced' => false,
        ];
    }
}

