<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table): void {
            $table->string('face_reference_path')->nullable();
        });

        Schema::table('attendances', function (Blueprint $table): void {
            $table->dateTime('checked_in_at')->nullable();
            $table->dateTime('checked_out_at')->nullable();
            $table->unsignedSmallInteger('check_in_distance_m')->nullable();
            $table->unsignedSmallInteger('check_out_distance_m')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table): void {
            $table->dropColumn(['checked_in_at', 'checked_out_at', 'check_in_distance_m', 'check_out_distance_m']);
        });
        Schema::table('employees', function (Blueprint $table): void {
            $table->dropColumn('face_reference_path');
        });
    }
};
