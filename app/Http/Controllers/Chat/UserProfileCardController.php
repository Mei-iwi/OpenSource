<?php

namespace App\Http\Controllers\Chat;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserProfileCardController extends Controller
{
    /**
     * Lấy thông tin hồ sơ danh thiếp nội bộ công khai, an toàn của nhân sự.
     * TUYỆT ĐỐI KHÔNG để lộ thông tin nhạy cảm (CCCD, lương, thuế, bảo hiểm, địa chỉ riêng tư).
     */
    public function show(Request $request, User $user): JsonResponse
    {
        $currentUser = $request->user();

        // Nạp quan hệ cần thiết
        $user->loadMissing(['employee.department']);

        $roleLabels = [
            'admin' => 'Quản trị viên',
            'hr' => 'Nhân sự (HR)',
            'manager' => 'Trưởng phòng / Quản lý',
            'employee' => 'Nhân viên',
        ];

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'avatar_url' => $user->avatar_url,
            'role' => $user->role,
            'role_label' => $roleLabels[$user->role] ?? ucfirst($user->role),
            'department' => $user->employee?->department?->name ?? 'Chưa phân bổ',
            'position' => $user->employee?->position ?? 'Nhân viên',
            'phone' => $user->employee?->phone ?? 'Chưa cập nhật',
            'can_dm' => $currentUser->id !== $user->id && $user->account_status === 'active',
            'dm_url' => route('chat.direct.open', $user->id),
        ]);
    }
}
