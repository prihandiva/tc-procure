<?php

namespace Database\Factories;

use App\Enums\DocumentType;
use App\Models\Document;
use App\Models\PurchaseOrder;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Document>
 */
class DocumentFactory extends Factory
{
    protected $model = Document::class;

    public function definition(): array
    {
        return [
            'documentable_type' => PurchaseOrder::class,
            'documentable_id' => PurchaseOrder::factory(),
            'type' => DocumentType::PO,
            'path' => 'documents/po_example.pdf',
            'original_name' => 'po_example.pdf',
            'mime' => 'application/pdf',
            'size' => 102400,
            'uploaded_by' => User::factory()->spv(),
        ];
    }
}

