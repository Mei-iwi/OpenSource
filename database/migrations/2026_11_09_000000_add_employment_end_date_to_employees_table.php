<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->date('employment_end_date')->nullable()->after('hire_date');
            $table->index('hire_date');
            $table->index('employment_end_date');
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropIndex(['hire_date']);
            $table->dropIndex(['employment_end_date']);
            $table->dropColumn('employment_end_date');
        });
    }
};
