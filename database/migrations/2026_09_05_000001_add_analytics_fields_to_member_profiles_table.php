<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('member_profiles', function (Blueprint $table) {
            $table->enum('education_level', ['spm', 'stpm', 'diploma', 'degree', 'masters', 'phd', 'others'])->nullable()->after('position');
            $table->enum('employment_status', ['permanent', 'contract', 'part_time', 'others'])->nullable()->after('education_level');
            $table->string('work_state', 50)->nullable()->after('employer_address');
        });
    }

    public function down(): void
    {
        Schema::table('member_profiles', function (Blueprint $table) {
            $table->dropColumn(['education_level', 'employment_status', 'work_state']);
        });
    }
};
