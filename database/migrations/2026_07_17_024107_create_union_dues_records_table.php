<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('union_dues_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mandate_id')->constrained('union_dues_mandates')->cascadeOnDelete();
            $table->string('period_month', 7);
            $table->decimal('amount_received', 10, 2);
            $table->foreignId('recorded_by')->constrained('users')->restrictOnDelete();
            $table->timestamp('recorded_at');
            $table->text('notes')->nullable();

            $table->unique(['mandate_id', 'period_month'], 'uniq_mandate_period');
            $table->index('period_month');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('union_dues_records');
    }
};
