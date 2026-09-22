<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Mở rộng enum type trong chat_channels để hỗ trợ 'direct' (1-1)
        DB::statement("ALTER TABLE chat_channels MODIFY COLUMN type ENUM('company', 'department', 'group', 'direct') NOT NULL DEFAULT 'group'");

        // 2. Bảng lưu trữ cảm xúc (reactions) trên tin nhắn
        Schema::create('chat_message_reactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('message_id')->constrained('chat_messages')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('reaction', 32);
            $table->timestamps();

            $table->unique(['message_id', 'user_id', 'reaction'], 'chat_reactions_msg_user_reaction_unique');
            $table->index('message_id');
        });

        // 3. Bảng lưu trữ tệp đính kèm và hình ảnh cho tin nhắn
        Schema::create('chat_message_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('message_id')->constrained('chat_messages')->cascadeOnDelete();
            $table->string('file_path');
            $table->string('file_name');
            $table->unsignedBigInteger('file_size');
            $table->string('mime_type', 100);
            $table->boolean('is_image')->default(false);
            $table->timestamps();

            $table->index('message_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_message_attachments');
        Schema::dropIfExists('chat_message_reactions');

        // Xóa các kênh direct trước khi rollback enum type
        DB::table('chat_channels')->where('type', 'direct')->delete();
        DB::statement("ALTER TABLE chat_channels MODIFY COLUMN type ENUM('company', 'department', 'group') NOT NULL DEFAULT 'group'");
    }
};
