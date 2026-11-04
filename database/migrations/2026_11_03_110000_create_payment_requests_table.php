<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_requests', function (Blueprint $table) {
            $table->id();
            $table->string('number')->unique(); // e.g. TC/PC/10-X/2025/509
            $table->foreignId('current_stage_id')->constrained('payment_stages')->restrictOnDelete();
            $table->timestamp('submitted_at')->nullable()->index();
            $table->text('notes')->nullable();
            $table->foreignId('posting_id')->nullable()->constrained('postings')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['current_stage_id', 'posting_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_requests');
    }
};

