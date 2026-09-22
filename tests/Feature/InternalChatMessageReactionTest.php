<?php

namespace Tests\Feature;

use App\Models\ChatChannel;
use App\Models\ChatChannelMember;
use App\Models\ChatMessage;
use App\Models\ChatMessageReaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class InternalChatMessageReactionTest extends TestCase
{
    use RefreshDatabase;

    private int $seq = 1;

    protected function createUser(string $role = 'employee', array $attrs = []): User
    {
        $id = $this->seq++;

        return User::create(array_merge([
            'name' => "User {$role} {$id}",
            'email' => "reaction_user{$id}_{$role}@example.com",
            'password' => Hash::make('Password123!'),
            'role' => $role,
            'account_status' => 'active',
        ], $attrs));
    }

    protected function createChannel(User ...$members): ChatChannel
    {
        $channel = ChatChannel::create([
            'name' => 'Kênh Cảm Xúc '.$this->seq++,
            'slug' => 'reaction-chan-'.$this->seq,
            'type' => 'group',
        ]);

        foreach ($members as $user) {
            ChatChannelMember::create([
                'channel_id' => $channel->id,
                'user_id' => $user->id,
                'joined_at' => now(),
            ]);
        }

        return $channel;
    }

    public function test_member_can_toggle_reaction_on_message(): void
    {
        $user = $this->createUser('employee');
        $channel = $this->createChannel($user);

        $message = ChatMessage::create([
            'channel_id' => $channel->id,
            'user_id' => $user->id,
            'message' => 'Ý tưởng dự án tuyệt vời!',
        ]);

        // 1. Lần đầu thả 👍 -> tạo mới
        $response1 = $this->actingAs($user)->postJson(route('chat.messages.reactions.toggle', $message->id), [
            'reaction' => '👍',
        ]);

        $response1->assertOk();
        $response1->assertJsonPath('success', true);
        $this->assertDatabaseHas('chat_message_reactions', [
            'message_id' => $message->id,
            'user_id' => $user->id,
            'reaction' => '👍',
        ]);

        // 2. Lần thứ hai thả cùng biểu cảm 👍 -> tự động gỡ bỏ (toggle off)
        $response2 = $this->actingAs($user)->postJson(route('chat.messages.reactions.toggle', $message->id), [
            'reaction' => '👍',
        ]);

        $response2->assertOk();
        $this->assertDatabaseMissing('chat_message_reactions', [
            'message_id' => $message->id,
            'user_id' => $user->id,
            'reaction' => '👍',
        ]);
    }

    public function test_multiple_users_can_react_without_duplicates(): void
    {
        $user1 = $this->createUser('employee');
        $user2 = $this->createUser('employee');
        $channel = $this->createChannel($user1, $user2);

        $message = ChatMessage::create([
            'channel_id' => $channel->id,
            'user_id' => $user1->id,
            'message' => 'Hoan hô team!',
        ]);

        $this->actingAs($user1)->postJson(route('chat.messages.reactions.toggle', $message->id), [
            'reaction' => '🎉',
        ])->assertOk();

        $this->flushSession();

        $response = $this->actingAs($user2)->postJson(route('chat.messages.reactions.toggle', $message->id), [
            'reaction' => '🎉',
        ]);

        $response->assertOk();
        $this->assertEquals(2, ChatMessageReaction::where('message_id', $message->id)->where('reaction', '🎉')->count());
    }

    public function test_non_member_cannot_react_to_private_channel_message(): void
    {
        $member = $this->createUser('employee');
        $nonMember = $this->createUser('employee');
        $channel = $this->createChannel($member);

        $message = ChatMessage::create([
            'channel_id' => $channel->id,
            'user_id' => $member->id,
            'message' => 'Nội bộ bí mật',
        ]);

        $this->flushSession();
        $response = $this->actingAs($nonMember)->postJson(route('chat.messages.reactions.toggle', $message->id), [
            'reaction' => '❤️',
        ]);

        $response->assertForbidden();
        $this->assertDatabaseMissing('chat_message_reactions', [
            'message_id' => $message->id,
            'reaction' => '❤️',
        ]);
    }

    public function test_invalid_reaction_is_rejected(): void
    {
        $user = $this->createUser('employee');
        $channel = $this->createChannel($user);

        $message = ChatMessage::create([
            'channel_id' => $channel->id,
            'user_id' => $user->id,
            'message' => 'Tin nhắn thường',
        ]);

        $response = $this->actingAs($user)->postJson(route('chat.messages.reactions.toggle', $message->id), [
            'reaction' => 'NOT_AN_ALLOWED_EMOJI',
        ]);

        $response->assertUnprocessable();
    }
}
