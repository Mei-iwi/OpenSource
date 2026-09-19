<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_code_sequences', function (Blueprint $table) {
            $table->string('prefix', 10)->primary();
            $table->unsignedBigInteger('last_number')->default(0);
        });

        foreach (['EMP', 'HR'] as $prefix) {
            $last = 0;
            foreach (DB::table('employees')->where('employee_code', 'like', $prefix.'-%')->pluck('employee_code') as $code) {
                if (preg_match('/^'.$prefix.'-(\d+)$/i', $code, $matches)) {
                    $last = max($last, (int) $matches[1]);
                }
            }
            DB::table('employee_code_sequences')->insert(['prefix' => $prefix, 'last_number' => $last]);
        }

        Schema::create('user_role_changes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('from_role', 30);
            $table->string('to_role', 30);
            $table->string('note');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_role_changes');
        Schema::dropIfExists('employee_code_sequences');
    }
};
