<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admin_messages', function (Blueprint $table): void {
            $table->text('subject')->change();
        });

    }

    public function down(): void
    {
        // Keep stored messages encrypted on rollback.
    }
};
