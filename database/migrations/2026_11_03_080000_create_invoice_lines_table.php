<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices')->cascadeOnDelete();
            $table->foreignId('delivery_item_id')->constrained('delivery_items')->cascadeOnDelete();
            $table->decimal('qty', 12, 2);
            $table->unsignedBigInteger('unit_price'); // PO price snapshot
            $table->unsignedBigInteger('amount'); // qty * unit_price
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['invoice_id', 'delivery_item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_lines');
    }
};

