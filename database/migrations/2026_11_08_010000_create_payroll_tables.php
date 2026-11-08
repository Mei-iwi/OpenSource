<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_salaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->unique()->constrained('employees')->restrictOnDelete();
            $table->decimal('base_salary', 18, 2);
            $table->decimal('allowance', 18, 2)->default(0);
            $table->decimal('fixed_deduction', 18, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('payroll_periods', function (Blueprint $table) {
            $table->id();
            $table->date('period_month')->unique();
            $table->string('status', 20)->default('draft');
            $table->foreignId('finalized_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('finalized_at')->nullable();
            $table->timestamps();
        });

        Schema::create('payslips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_period_id')->constrained('payroll_periods')->restrictOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->restrictOnDelete();
            $table->string('employee_name');
            $table->string('employee_code', 30);
            $table->unsignedSmallInteger('scheduled_days');
            $table->unsignedSmallInteger('worked_days');
            $table->unsignedSmallInteger('paid_leave_days');
            $table->unsignedSmallInteger('unpaid_leave_days');
            $table->unsignedSmallInteger('unpaid_days');
            $table->decimal('base_salary', 18, 2);
            $table->decimal('base_pay', 18, 2);
            $table->decimal('attendance_deduction', 18, 2);
            $table->decimal('allowance', 18, 2);
            $table->decimal('fixed_deduction', 18, 2);
            $table->decimal('gross_pay', 18, 2);
            $table->decimal('net_pay', 18, 2);
            $table->timestamps();
            $table->unique(['payroll_period_id', 'employee_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payslips');
        Schema::dropIfExists('payroll_periods');
        Schema::dropIfExists('employee_salaries');
    }
};
