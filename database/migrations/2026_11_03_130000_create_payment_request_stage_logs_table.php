<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_request_stage_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_request_id')->constrained('payment_requests')->cascadeOnDelete();
            $table->foreignId('from_stage_id')->nullable()->constrained('payment_stages')->nullOnDelete();
            $table->foreignId('to_stage_id')->constrained('payment_stages')->cascadeOnDelete();
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamp('created_at')->useCurrent()->index();

            $table->index(['payment_request_id', 'to_stage_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_request_stage_logs');
    }
};

