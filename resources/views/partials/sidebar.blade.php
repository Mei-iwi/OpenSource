@php($role = auth()->check() ? (auth()->user()->role ?? null) : null)
<div class="flex min-h-screen flex-col">
    <div class="border-b border-slate-200 px-6 py-6">
        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-indigo-600">Workspace</p>
        <h2 class="mt-2 text-xl font-bold text-slate-900">Quản lý nhân sự</h2>
        <p class="mt-1 text-sm text-slate-500">Thiết kế giao diện nền</p>
    </div>
    <nav class="flex-1 space-y-7 px-4 py-6" aria-label="Điều hướng chính">
        <div>
            <p class="px-3 text-xs font-semibold uppercase tracking-wider text-slate-400">Tổng quan</p>
            <div class="mt-2 space-y-1">
                <a href="{{ route('preview.admin') }}" class="block rounded-lg px-3 py-2.5 text-sm font-medium text-slate-700 hover:bg-indigo-50 hover:text-indigo-700">Dashboard Admin</a>
                <a href="{{ route('preview.hr') }}" class="block rounded-lg px-3 py-2.5 text-sm font-medium text-slate-700 hover:bg-indigo-50 hover:text-indigo-700">Dashboard HR</a>
                <a href="{{ route('preview.employee') }}" class="block rounded-lg px-3 py-2.5 text-sm font-medium text-slate-700 hover:bg-indigo-50 hover:text-indigo-700">Dashboard Employee</a>
            </div>
        </div>
        <div>
            <p class="px-3 text-xs font-semibold uppercase tracking-wider text-slate-400">Khu vực quản lý</p>
            <div class="mt-2 space-y-1">
                <a href="{{ Route::has('admin.users.index') ? route('admin.users.index') : route('preview.admin') }}" class="block rounded-lg px-3 py-2.5 text-sm font-medium text-slate-700 hover:bg-indigo-50 hover:text-indigo-700">Tài khoản & vai trò</a>
                <a href="{{ route('preview.hr') }}#departments" class="block rounded-lg px-3 py-2.5 text-sm font-medium text-slate-700 hover:bg-indigo-50 hover:text-indigo-700">Phòng ban</a>
                <a href="{{ route('preview.hr') }}#employees" class="block rounded-lg px-3 py-2.5 text-sm font-medium text-slate-700 hover:bg-indigo-50 hover:text-indigo-700">Nhân viên</a>
                <a href="{{ route('preview.hr') }}#attendances" class="block rounded-lg px-3 py-2.5 text-sm font-medium text-slate-700 hover:bg-indigo-50 hover:text-indigo-700">Chấm công</a>
                <a href="{{ route('preview.hr') }}#reports" class="block rounded-lg px-3 py-2.5 text-sm font-medium text-slate-700 hover:bg-indigo-50 hover:text-indigo-700">Báo cáo</a>
            </div>
        </div>
        <div>
            <p class="px-3 text-xs font-semibold uppercase tracking-wider text-slate-400">Cá nhân</p>
            <div class="mt-2 space-y-1">
                <a href="{{ route('preview.employee') }}#profile" class="block rounded-lg px-3 py-2.5 text-sm font-medium text-slate-700 hover:bg-indigo-50 hover:text-indigo-700">Hồ sơ của tôi</a>
                <a href="{{ route('preview.employee') }}#attendance" class="block rounded-lg px-3 py-2.5 text-sm font-medium text-slate-700 hover:bg-indigo-50 hover:text-indigo-700">Lịch sử chấm công</a>
            </div>
        </div>
    </nav>
    <div class="border-t border-slate-200 p-4">
        <div class="rounded-xl bg-slate-50 p-3 text-xs text-slate-500">Vai trò hiện tại: <span class="font-semibold text-slate-700">{{ $role ?? 'preview' }}</span></div>
    </div>
</div>
