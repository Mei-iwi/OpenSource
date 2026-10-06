<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $filter = $request->query('filter', 'all');

        $query = $filter === 'unread'
            ? $user->unreadNotifications()
            : $user->notifications();

        $notifications = $query->paginate(15)->withQueryString();
        $unreadCount = $user->unreadNotifications()->count();

        return view('notifications.index', [
            'notifications' => $notifications,
            'unreadCount' => $unreadCount,
            'filter' => $filter,
        ]);
    }

    public function unreadCount(Request $request): JsonResponse
    {
        return response()->json([
            'count' => $request->user()->unreadNotifications()->count(),
        ]);
    }

    public function markAsRead(Request $request, string $id): RedirectResponse|JsonResponse
    {
        $user = $request->user();
        $notification = $user->notifications()->where('id', $id)->first();

        if (! $notification) {
            $otherNotification = DatabaseNotification::find($id);
            if ($otherNotification) {
                abort(403, 'Bạn không có quyền thao tác trên thông báo này.');
            }
            abort(404, 'Không tìm thấy thông báo.');
        }

        if (! $notification->read_at) {
            $notification->markAsRead();
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'unread_count' => $user->unreadNotifications()->count(),
            ]);
        }

        $redirectTo = $request->input('redirect_to') ?? ($notification->data['url'] ?? null);
        if ($redirectTo) {
            return redirect($redirectTo);
        }

        return back()->with('success', 'Đã đánh dấu thông báo là đã đọc.');
    }

    public function markAllAsRead(Request $request): RedirectResponse|JsonResponse
    {
        $user = $request->user();
        $user->unreadNotifications->markAsRead();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'unread_count' => 0,
            ]);
        }

        return back()->with('success', 'Đã đánh dấu tất cả thông báo là đã đọc.');
    }
}
