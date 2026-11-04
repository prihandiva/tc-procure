<?php

namespace Database\Factories;

use App\Models\DeliveryItem;
use App\Models\Invoice;
use App\Models\InvoiceLine;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InvoiceLine>
 */
class InvoiceLineFactory extends Factory
{
    protected $model = InvoiceLine::class;

    public function definition(): array
    {
        return [
            'invoice_id' => Invoice::factory(),
            'delivery_item_id' => DeliveryItem::factory(),
            'qty' => 10.00,
            'unit_price' => 3500,
            'amount' => 35000,
        ];
    }
}

