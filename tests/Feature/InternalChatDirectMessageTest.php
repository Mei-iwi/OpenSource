<?php

namespace Tests\Feature;

use App\Models\ChatChannel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class InternalChatDirectMessageTest extends TestCase
{
    use RefreshDatabase;

    private int $seq = 1;

    protected function createUser(string $role = 'employee', array $attrs = []): User
    {
        $id = $this->seq++;

        return User::create(array_merge([
            'name' => "Direct User {$role} {$id}",
            'email' => "dm_user{$id}_{$role}@example.com",
            'password' => Hash::make('Password123!'),
            'role' => $role,
            'account_status' => 'active',
        ], $attrs));
    }

    public function test_two_users_open_same_deterministic_direct_channel(): void
    {
        $userA = $this->createUser('employee');
        $userB = $this->createUser('employee');

        $minId = min($userA->id, $userB->id);
        $maxId = max($userA->id, $userB->id);
        $expectedSlug = "dm-{$minId}-{$maxId}";

        // 1. User A mở chat với User B
        $responseA = $this->actingAs($userA)->post(route('chat.direct.open', $userB->id));
        $responseA->assertRedirect(route('chat.channels.show', $expectedSlug));

        $channelA = ChatChannel::where('slug', $expectedSlug)->first();
        $this->assertNotNull($channelA);
        $this->assertEquals('direct', $channelA->type);
        $this->assertTrue($channelA->hasMember($userA->id));
        $this->assertTrue($channelA->hasMember($userB->id));

        // 2. User B mở chat ngược lại với User A -> phải tái sử dụng đúng channel cũ
        $this->flushSession();
        $responseB = $this->actingAs($userB)->post(route('chat.direct.open', $userA->id));
        $responseB->assertRedirect(route('chat.channels.show', $expectedSlug));

        $this->assertEquals(1, ChatChannel::where('slug', $expectedSlug)->count());
    }

    public function test_user_cannot_direct_chat_with_self(): void
    {
        $user = $this->createUser('employee');

        $response = $this->actingAs($user)->postJson(route('chat.direct.open', $user->id));

        $response->assertUnprocessable();
        $this->assertEquals(0, ChatChannel::where('type', 'direct')->count());
    }

    public function test_third_party_cannot_access_or_send_in_direct_channel(): void
    {
        $userA = $this->createUser('employee');
        $userB = $this->createUser('employee');
        $userC = $this->createUser('employee');

        $this->actingAs($userA)->post(route('chat.direct.open', $userB->id));
        $minId = min($userA->id, $userB->id);
        $maxId = max($userA->id, $userB->id);
        $slug = "dm-{$minId}-{$maxId}";

        // User C cố tình xem kênh riêng của A & B
        $this->flushSession();
        $viewResponse = $this->actingAs($userC)->get(route('chat.channels.show', $slug));
        $viewResponse->assertForbidden();

        // User C cố tình gửi tin vào kênh riêng của A & B
        $sendResponse = $this->actingAs($userC)->postJson(route('chat.channels.messages.store', $slug), [
            'message' => 'Nghe lỏm cuộc trò chuyện',
        ]);
        $sendResponse->assertForbidden();
    }

    public function test_direct_channel_cannot_be_deleted_via_admin_channel_endpoint(): void
    {
        $admin = $this->createUser('admin');
        $userA = $this->createUser('employee');
        $userB = $this->createUser('employee');

        $this->actingAs($userA)->post(route('chat.direct.open', $userB->id));
        $minId = min($userA->id, $userB->id);
        $maxId = max($userA->id, $userB->id);
        $slug = "dm-{$minId}-{$maxId}";

        $this->flushSession();
        $response = $this->actingAs($admin)->delete(route('chat.channels.destroy', $slug));
        $response->assertForbidden();
    }
}
