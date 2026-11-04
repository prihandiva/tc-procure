<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_id')->constrained('deliveries')->cascadeOnDelete();
            $table->foreignId('po_item_id')->constrained('po_items')->cascadeOnDelete();
            $table->decimal('qty_sent', 12, 2);
            $table->decimal('qty_received', 12, 2)->nullable();
            $table->string('training_name')->nullable()->index();
            $table->date('needed_date')->nullable()->index();
            $table->text('variant_note')->nullable(); // e.g. "12 lontong, 12 pudding, 12 bolen, 12 roll"
            $table->boolean('is_invoiced')->default(false)->index();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['delivery_id', 'po_item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_items');
    }
};

