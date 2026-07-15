<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('member_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('card_number', 50)->unique();
            $table->string('qr_token', 100)->unique()->comment('Random token for the public verification URL');
            $table->string('file_path')->nullable()->comment('Path to the generated card PDF/image');
            $table->date('issued_at');
            $table->date('expires_at');
            $table->timestamps();

            $table->index('qr_token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('member_cards');
    }
};
