<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN member_status ENUM('active', 'retired', 'suspended') NOT NULL DEFAULT 'active'");

        Schema::table('member_profiles', function (Blueprint $table) {
            $table->string('union_branch', 100)->nullable()->after('bank_branch');
        });
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN member_status ENUM('active', 'retired') NOT NULL DEFAULT 'active'");

        Schema::table('member_profiles', function (Blueprint $table) {
            $table->dropColumn('union_branch');
        });
    }
};
