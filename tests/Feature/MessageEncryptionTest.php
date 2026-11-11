<?php

namespace Tests\Feature;

use App\Models\AdminMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MessageEncryptionTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_message_subject_and_body_are_encrypted_at_rest(): void
    {
        $admin = User::create([
            'name' => 'Admin', 'email' => 'admin@example.test', 'password' => 'Password123!',
            'role' => 'admin', 'account_status' => 'active',
        ]);

        $message = AdminMessage::create([
            'admin_id' => $admin->id,
            'subject' => 'Private subject',
            'body' => 'Private body',
            'audience' => 'all',
        ]);
        $stored = DB::table('admin_messages')->where('id', $message->id)->first();
        $this->assertStringStartsWith('enc:v1:', $stored->subject);
        $this->assertStringStartsWith('enc:v1:', $stored->body);
        $this->assertStringNotContainsString('Private', $stored->body);
        $this->assertSame('Private subject', $message->fresh()->subject);
        $this->assertSame('Private body', $message->fresh()->body);
    }

    public function test_legacy_admin_message_migrates_without_double_encryption(): void
    {
        $admin = User::create([
            'name' => 'Admin', 'email' => 'admin@example.test', 'password' => 'Password123!',
            'role' => 'admin', 'account_status' => 'active',
        ]);
        $id = DB::table('admin_messages')->insertGetId([
            'admin_id' => $admin->id, 'subject' => 'Legacy subject', 'body' => 'Legacy body', 'audience' => 'all',
        ]);
        $migration = require database_path('migrations/2026_11_10_030000_backfill_admin_message_encryption.php');
        $migration->up();

        $stored = DB::table('admin_messages')->where('id', $id)->first();
        $this->assertStringStartsWith('enc:v1:', $stored->subject);
        $this->assertStringStartsWith('enc:v1:', $stored->body);
        $this->assertSame('Legacy subject', AdminMessage::findOrFail($id)->subject);
        $this->assertSame('Legacy body', AdminMessage::findOrFail($id)->body);

        $migration->up();
        $this->assertSame($stored->subject, DB::table('admin_messages')->where('id', $id)->value('subject'));
    }
}
