<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->string('contract_type', 20)->nullable()->after('hire_date');
            $table->date('contract_start_date')->nullable()->after('contract_type');
            $table->text('identity_number')->nullable();
            $table->text('tax_code')->nullable();
            $table->text('social_insurance_number')->nullable();
            $table->text('emergency_contact_name')->nullable();
            $table->text('emergency_contact_phone')->nullable();
            $table->text('emergency_contact_relationship')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn([
                'contract_type', 'contract_start_date', 'identity_number', 'tax_code',
                'social_insurance_number', 'emergency_contact_name',
                'emergency_contact_phone', 'emergency_contact_relationship',
            ]);
        });
    }
};
