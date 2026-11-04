<?php

namespace Database\Factories;

use App\Enums\PoStatus;
use App\Enums\PoType;
use App\Models\PurchaseOrder;
use App\Models\Vendor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PurchaseOrder>
 */
class PurchaseOrderFactory extends Factory
{
    protected $model = PurchaseOrder::class;

    public function definition(): array
    {
        return [
            'po_number' => 'PO-'.fake()->unique()->numerify('########'),
            'type' => PoType::PO,
            'vendor_id' => Vendor::factory(),
            'po_date' => fake()->date(),
            'status' => PoStatus::ACTIVE,
            'notes' => fake()->sentence(),
        ];
    }

    public function nonPo(): static
    {
        return $this->state(fn () => [
            'type' => PoType::NON_PO,
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn () => [
            'status' => PoStatus::COMPLETED,
        ]);
    }
}

