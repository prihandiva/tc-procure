<?php

namespace Database\Factories;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use App\Models\PurchaseOrder;
use App\Models\Vendor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Invoice>
 */
class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    public function definition(): array
    {
        return [
            'invoice_number' => 'INV-'.fake()->unique()->numerify('#####'),
            'vendor_id' => Vendor::factory(),
            'purchase_order_id' => PurchaseOrder::factory(),
            'invoice_date' => fake()->date(),
            'total_amount' => 150000,
            'status' => InvoiceStatus::SUBMITTED,
        ];
    }

    public function approved(): static
    {
        return $this->state(fn () => [
            'status' => InvoiceStatus::APPROVED,
        ]);
    }

    public function paid(): static
    {
        return $this->state(fn () => [
            'status' => InvoiceStatus::PAID,
        ]);
    }
}

