<?php

namespace App\Http\Controllers\Chat;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreChatChannelRequest;
use App\Http\Requests\UpdateChatChannelRequest;
use App\Models\ChatChannel;
use App\Models\Department;
use App\Models\User;
use App\Services\ChatService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ChatChannelController extends Controller
{
    public function __construct(protected ChatService $chatService)
    {
    }

    /**
     * Màn hình form tạo kênh mới (cho Admin/HR).
     */
    public function create(Request $request): View
    {
        $this->authorize('create', ChatChannel::class);

        $departments = Department::orderBy('name')->get();
        $employees = User::where('account_status', 'active')
            ->with('employee.department')
            ->orderBy('name')
            ->get();

        return view('chat.channels.create', [
            'departments' => $departments,
            'employees' => $employees,
        ]);
    }

    /**
     * Lưu kênh mới vào database.
     */
    public function store(StoreChatChannelRequest $request): RedirectResponse
    {
        $this->authorize('create', ChatChannel::class);

        $channel = $this->chatService->createChannel(
            $request->validated(),
            $request->user()
        );

        return redirect()
            ->route('chat.channels.show', $channel->slug)
            ->with('success', "Đã tạo kênh #{$channel->slug} thành công.");
    }

    /**
     * Màn hình cập nhật thông tin kênh.
     */
    public function edit(Request $request, ChatChannel $channel): View
    {
        $this->authorize('update', $channel);

        $departments = Department::orderBy('name')->get();

        return view('chat.channels.edit', [
            'channel' => $channel,
            'departments' => $departments,
        ]);
    }

    /**
     * Cập nhật thông tin kênh.
     */
    public function update(UpdateChatChannelRequest $request, ChatChannel $channel): RedirectResponse
    {
        $this->authorize('update', $channel);

        $channel->update($request->validated());

        return redirect()
            ->route('chat.channels.show', $channel->slug)
            ->with('success', 'Đã cập nhật thông tin kênh thành công.');
    }

    /**
     * Xóa kênh chat (không áp dụng cho kênh mặc định).
     */
    public function destroy(Request $request, ChatChannel $channel): RedirectResponse
    {
        $this->authorize('delete', $channel);

        if ($channel->isCompany()) {
            abort(403, 'Kênh toàn công ty mặc định không thể bị xóa.');
        }

        $name = $channel->name;
        $channel->delete();

        return redirect()
            ->route('chat.index')
            ->with('success', "Đã xóa kênh {$name} thành công.");
    }
}
