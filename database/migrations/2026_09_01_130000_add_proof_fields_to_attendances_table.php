<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->string('check_in_photo_path')->nullable()->after('check_in');
            $table->string('check_out_photo_path')->nullable()->after('check_out');
            $table->string('check_in_method')->nullable()->after('check_in_photo_path');
            $table->string('check_out_method')->nullable()->after('check_out_photo_path');
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn(['check_in_photo_path', 'check_out_photo_path', 'check_in_method', 'check_out_method']);
        });
    }
};
