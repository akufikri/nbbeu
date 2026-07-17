<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('member_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();

            $table->enum('gender', ['male', 'female'])->nullable();
            $table->enum('race', ['malay', 'chinese', 'indian', 'bumiputra'])->nullable();
            $table->string('race_sub_group', 50)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('place_of_birth')->nullable();
            $table->text('ic_no')->nullable();

            $table->text('postal_address')->nullable();
            $table->text('residential_address')->nullable();

            $table->string('occupation')->nullable();
            $table->string('position')->nullable();
            $table->string('employer_name')->nullable();
            $table->text('employer_address')->nullable();
            $table->date('employment_date')->nullable();

            $table->string('bank_name')->nullable();
            $table->string('bank_branch')->nullable();
            $table->text('bank_address')->nullable();

            $table->string('office_tel', 20)->nullable();
            $table->string('office_fax', 20)->nullable();

            $table->text('present_salary')->nullable();
            $table->date('salary_increment_date')->nullable();

            // Nullable at DB level (tolerant for admin-imported legacy members
            // per DATABASE.md §12) — wizard registration enforces required via validation.
            $table->foreignId('proposed_by_user_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->foreignId('seconded_by_user_id')->nullable()->constrained('users')->restrictOnDelete();

            $table->timestamp('declaration_accepted_at')->nullable();
            $table->string('signature_path')->nullable();

            $table->timestamps();

            $table->index('proposed_by_user_id');
            $table->index('seconded_by_user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('member_profiles');
    }
};
