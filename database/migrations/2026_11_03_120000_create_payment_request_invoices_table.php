<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_request_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_request_id')->constrained('payment_requests')->cascadeOnDelete();
            $table->foreignId('invoice_id')->constrained('invoices')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['payment_request_id', 'invoice_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_request_invoices');
    }
};

