<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('union_dues_mandates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->decimal('deduction_amount', 10, 2);
            $table->string('consent_file_path');
            $table->enum('status', ['pending_submission', 'active', 'cancelled'])->default('pending_submission');
            $table->timestamp('consent_signed_at');
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('union_dues_mandates');
    }
};
