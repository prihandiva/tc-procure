<?php

namespace Database\Factories;

use App\Enums\DeliveryStatus;
use App\Models\Delivery;
use App\Models\PurchaseOrder;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Delivery>
 */
class DeliveryFactory extends Factory
{
    protected $model = Delivery::class;

    public function definition(): array
    {
        return [
            'code' => 'DLV-'.fake()->unique()->numerify('2026-#####'),
            'purchase_order_id' => fn (array $attrs) => isset($attrs['vendor_id'])
                ? PurchaseOrder::factory()->create(['vendor_id' => $attrs['vendor_id']])->id
                : PurchaseOrder::factory(),
            'vendor_id' => fn (array $attrs) => PurchaseOrder::find($attrs['purchase_order_id'])?->vendor_id ?? Vendor::factory(),
            'delivery_date' => fake()->date(),
            'vendor_do_number' => fake()->numerify('DO-#####'),
            'status' => DeliveryStatus::SUBMITTED,
            'submitted_by' => User::factory()->vendor(),
            'notes' => fake()->sentence(),
        ];
    }

    public function confirmed(): static
    {
        return $this->state(fn () => [
            'status' => DeliveryStatus::CONFIRMED,
            'confirmed_by' => User::factory()->spv(),
            'confirmed_at' => now(),
        ]);
    }
}

