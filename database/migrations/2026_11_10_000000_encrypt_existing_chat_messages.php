<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('chat_messages')->select(['id', 'message'])->orderBy('id')->chunkById(500, function ($rows): void {
            foreach ($rows as $row) {
                if (str_starts_with($row->message, 'enc:v1:')) {
                    continue;
                }

                DB::table('chat_messages')->where('id', $row->id)->update([
                    'message' => 'enc:v1:'.Crypt::encryptString($row->message),
                ]);
            }
        });
    }

    public function down(): void
    {
        // Keep stored messages encrypted on rollback.
    }
};
