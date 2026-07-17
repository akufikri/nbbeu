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
        Schema::table('member_profiles', function (Blueprint $table) {
            // ic_no itself is encrypted (non-deterministic ciphertext), so a plain
            // unique index on it can't catch duplicates — this is a deterministic
            // blind index (sha256) used only for uniqueness lookups.
            $table->string('ic_no_hash', 64)->nullable()->unique()->after('ic_no');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('member_profiles', function (Blueprint $table) {
            $table->dropColumn('ic_no_hash');
        });
    }
};
