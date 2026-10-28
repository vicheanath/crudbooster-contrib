<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The HR example: departments, employees with skills, and leave requests. Managed by the
 * resources in app/Admin/Resources/Hr.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hr_departments', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('hr_skills', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        Schema::create('hr_employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hr_department_id')->constrained('hr_departments');
            $table->foreignId('manager_id')->nullable()->constrained('hr_employees')->nullOnDelete();
            $table->string('employee_number')->unique();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->string('job_title');
            $table->string('employment_type')->default('full_time');
            $table->date('hired_on');
            $table->decimal('salary', 12, 2)->default(0);
            $table->string('status')->default('active');
            $table->string('photo')->nullable();
            $table->boolean('is_remote')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('hr_employee_skill', function (Blueprint $table) {
            $table->foreignId('hr_employee_id')->constrained('hr_employees')->cascadeOnDelete();
            $table->foreignId('hr_skill_id')->constrained('hr_skills')->cascadeOnDelete();
            $table->primary(['hr_employee_id', 'hr_skill_id']);
        });

        Schema::create('hr_leave_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hr_employee_id')->constrained('hr_employees')->cascadeOnDelete();
            $table->string('type')->default('annual');
            $table->date('starts_on');
            $table->date('ends_on');
            $table->text('reason')->nullable();
            $table->string('status')->default('pending');
            $table->dateTime('decided_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hr_leave_requests');
        Schema::dropIfExists('hr_employee_skill');
        Schema::dropIfExists('hr_employees');
        Schema::dropIfExists('hr_skills');
        Schema::dropIfExists('hr_departments');
    }
};
