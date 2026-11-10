<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('admin_messages')->select(['id', 'subject', 'body'])->orderBy('id')->chunkById(500, function ($rows): void {
            foreach ($rows as $row) {
                $changes = [];
                foreach (['subject', 'body'] as $field) {
                    if (! str_starts_with($row->{$field}, 'enc:v1:')) {
                        $changes[$field] = 'enc:v1:'.Crypt::encryptString($row->{$field});
                    }
                }
                if ($changes) {
                    DB::table('admin_messages')->where('id', $row->id)->update($changes);
                }
            }
        });
    }

    public function down(): void
    {
        // Keep stored messages encrypted on rollback.
    }
};
