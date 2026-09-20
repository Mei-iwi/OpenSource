<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_positions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
        });

        $names = [
            'Quản trị hệ thống', 'Trưởng phòng Nhân sự', 'Chuyên viên Nhân sự',
            'Trưởng nhóm Phát triển', 'Lập trình viên Backend', 'Lập trình viên Frontend', 'Kỹ sư hệ thống',
            'Trưởng phòng Kinh doanh', 'Chuyên viên Kinh doanh', 'Nhân viên Kinh doanh',
            'Kế toán trưởng', 'Kế toán viên', 'Chuyên viên Tài chính',
            'Trưởng nhóm Chăm sóc khách hàng', 'Chuyên viên Chăm sóc khách hàng', 'Nhân viên Chăm sóc khách hàng',
            'Chuyên viên Hành chính', 'Nhân viên Hành chính', 'Thực tập sinh',
        ];
        // Preserve every existing title so editing a legacy profile loses no data.
        $names = collect($names)->merge(DB::table('employees')->whereNotNull('position')->pluck('position'))->filter()->unique();
        foreach ($names as $name) {
            DB::table('job_positions')->insertOrIgnore(['name' => $name]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('job_positions');
    }
};
