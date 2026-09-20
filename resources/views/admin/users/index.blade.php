@extends('layouts.app')
@section('title', 'Quản lý tài khoản')
@section('content')
<x-page-header eyebrow="Admin / Tài khoản" title="Quản lý tài khoản" description="Quản lý quyền truy cập. Tạo nhân viên và tài khoản tại mục Nhân viên."></x-page-header>
<div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
    <form method="GET" action="{{ route('admin.users.index') }}" class="mb-6 grid gap-3 md:grid-cols-[1fr_auto_auto_auto]"><label class="sr-only" for="user-search">Tìm user</label><input id="user-search" name="search" value="{{ request('search') }}" type="search" placeholder="Tìm theo tên hoặc email..." class="rounded-lg border border-slate-300 px-3 py-2 text-sm"><select name="role" class="rounded-lg border border-slate-300 px-3 py-2 text-sm"><option value="">Tất cả vai trò</option><option value="admin" @selected(request('role') === 'admin')>Admin</option><option value="hr" @selected(request('role') === 'hr')>HR</option><option value="employee" @selected(request('role') === 'employee')>Employee</option></select><select name="account_status" class="rounded-lg border border-slate-300 px-3 py-2 text-sm"><option value="">Tất cả trạng thái</option><option value="active" @selected(request('account_status') === 'active')>Đang hoạt động</option><option value="locked" @selected(request('account_status') === 'locked')>Đã khóa</option></select><button class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Lọc</button></form>
    <div class="grid gap-5 md:grid-cols-2 2xl:grid-cols-3">
    @forelse($users as $user)
        <article class="directory-card flex min-w-0 flex-col p-5">
            <div class="flex items-center gap-4"><x-user-avatar :user="$user" class="h-16 w-16 shrink-0" /><div class="min-w-0"><h2 class="break-words text-lg font-bold">{{ $user->name }}</h2><p class="mt-1 break-all text-sm text-[var(--app-muted)]">{{ $user->email }}</p></div></div>
            <div class="my-5 flex flex-wrap gap-2"><x-status-badge :status="$user->role" :label="['admin'=>'Quản trị viên','hr'=>'Nhân sự','employee'=>'Nhân viên'][$user->role] ?? $user->role" /><x-status-badge :status="$user->account_status" :label="$user->account_status === 'active' ? 'Đang hoạt động' : 'Đã khóa'" /></div>
            <div class="mt-auto flex flex-wrap items-center gap-3 border-t border-[var(--app-border)] pt-4">
                <a href="{{ route('admin.users.show', $user) }}" class="app-button-secondary">Chi tiết</a><a href="{{ route('admin.users.edit', $user) }}" class="font-semibold text-blue-600 dark:text-blue-300">Chỉnh sửa</a>
                @if($user->id !== auth()->id())<form method="POST" action="{{ route($user->account_status === 'active' ? 'admin.users.lock' : 'admin.users.unlock', $user) }}" class="ml-auto">@csrf @method('PATCH')<button onclick="return confirm('{{ $user->account_status === 'active' ? 'Khóa tài khoản này?' : 'Mở khóa tài khoản này?' }}')" class="text-sm font-semibold {{ $user->account_status === 'active' ? 'text-rose-600 dark:text-rose-300' : 'text-emerald-600 dark:text-emerald-300' }}">{{ $user->account_status === 'active' ? 'Khóa' : 'Mở khóa' }}</button></form>@endif
            </div>
        </article>
    @empty
        <div class="col-span-full"><x-empty-state title="Không tìm thấy tài khoản" description="Thử thay đổi từ khóa hoặc bộ lọc." /></div>
    @endforelse
</div><div class="mt-6">{{ $users->links() }}</div>
</div>
@endsection
