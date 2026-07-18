<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('member_profiles', function (Blueprint $table) {
            $table->dropForeign(['proposed_by_user_id']);
            $table->dropForeign(['seconded_by_user_id']);
            $table->dropColumn(['proposed_by_user_id', 'seconded_by_user_id']);

            $table->string('proposed_by_name')->nullable()->after('present_salary');
            $table->string('seconded_by_name')->nullable()->after('proposed_by_name');
        });
    }

    public function down(): void
    {
        Schema::table('member_profiles', function (Blueprint $table) {
            $table->dropColumn(['proposed_by_name', 'seconded_by_name']);

            $table->foreignId('proposed_by_user_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->foreignId('seconded_by_user_id')->nullable()->constrained('users')->restrictOnDelete();
        });
    }
};
