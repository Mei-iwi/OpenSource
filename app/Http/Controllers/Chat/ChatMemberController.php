<?php

namespace App\Http\Controllers\Chat;

use App\Http\Controllers\Controller;
use App\Http\Requests\ManageChatMemberRequest;
use App\Models\ChatChannel;
use App\Models\Department;
use App\Models\User;
use App\Services\ChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ChatMemberController extends Controller
{
    public function __construct(protected ChatService $chatService)
    {
    }

    /**
     * Danh sách thành viên của kênh.
     */
    public function index(Request $request, ChatChannel $channel): View|JsonResponse
    {
        $this->authorize('view', $channel);

        $channel->load(['members.user.employee.department']);
        $members = $channel->members;

        if ($request->wantsJson()) {
            return response()->json([
                'channel_id' => $channel->id,
                'members' => $members->map(fn ($m) => [
                    'id' => $m->id,
                    'user_id' => $m->user_id,
                    'name' => $m->user?->name,
                    'email' => $m->user?->email,
                    'role' => $m->user?->role,
                    'department' => $m->user?->employee?->department?->name,
                    'position' => $m->user?->employee?->position,
                    'avatar_url' => $m->user?->avatar_url,
                    'joined_at' => $m->joined_at?->format('d/m/Y H:i'),
                ]),
            ]);
        }

        $departments = Department::orderBy('name')->get();
        $availableUsers = User::where('account_status', 'active')
            ->whereNotIn('id', $channel->members->pluck('user_id'))
            ->with(['employee.department'])
            ->orderBy('name')
            ->get();

        return view('chat.channels.members', [
            'channel' => $channel,
            'members' => $members,
            'departments' => $departments,
            'availableUsers' => $availableUsers,
        ]);
    }

    /**
     * Thêm thành viên vào kênh.
     */
    public function store(ManageChatMemberRequest $request, ChatChannel $channel): RedirectResponse|JsonResponse
    {
        $this->authorize('manageMembers', $channel);

        if ($channel->isCompany()) {
            abort(403, 'Không thể chỉnh sửa thành viên của kênh toàn công ty.');
        }

        $userIds = $request->validated()['user_ids'];
        $this->chatService->addMembers($channel, $userIds);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Đã thêm thành viên vào kênh thành công.',
            ]);
        }

        return redirect()
            ->back()
            ->with('success', 'Đã thêm thành viên vào kênh thành công.');
    }

    /**
     * Xóa thành viên khỏi kênh.
     */
    public function destroy(Request $request, ChatChannel $channel, User $user): RedirectResponse|JsonResponse
    {
        $this->authorize('manageMembers', $channel);

        if ($channel->isCompany()) {
            abort(403, 'Không thể xóa thành viên khỏi kênh toàn công ty.');
        }

        $this->chatService->removeMember($channel, $user->id);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Đã xóa {$user->name} khỏi kênh.",
            ]);
        }

        return redirect()
            ->back()
            ->with('success', "Đã xóa {$user->name} khỏi kênh.");
    }
}
