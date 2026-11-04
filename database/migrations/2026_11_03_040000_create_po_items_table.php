<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('po_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained('purchase_orders')->cascadeOnDelete();
            $table->foreignId('item_id')->nullable()->constrained('items')->nullOnDelete();
            $table->string('item_name');
            $table->string('unit', 50)->default('pc');
            $table->decimal('qty_ordered', 12, 2);
            $table->unsignedBigInteger('unit_price'); // locked price in Rupiah
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['purchase_order_id', 'item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('po_items');
    }
};

