@extends('layouts.app')
@section('title', 'Chi tiết tài khoản')
@section('content')<x-page-header eyebrow="Admin / Tài khoản" title="Chi tiết tài khoản" description="Thông tin truy cập và trạng thái tài khoản."><a href="{{ route('admin.users.edit', $user) }}" class="rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white">Chỉnh sửa</a></x-page-header><div class="max-w-3xl rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"><dl class="grid gap-5 sm:grid-cols-2"><div><dt class="text-xs text-slate-500">Họ và tên</dt><dd class="mt-1 font-medium">{{ $user->name }}</dd></div><div><dt class="text-xs text-slate-500">Email</dt><dd class="mt-1 font-medium">{{ $user->email }}</dd></div><div><dt class="text-xs text-slate-500">Vai trò</dt><dd class="mt-2"><x-status-badge :status="$user->role" :label="strtoupper($user->role)" /></dd></div><div><dt class="text-xs text-slate-500">Trạng thái</dt><dd class="mt-2"><x-status-badge :status="$user->account_status" :label="$user->account_status === 'active' ? 'Đang hoạt động' : 'Đã khóa'" /></dd></div></dl><div class="mt-6 border-t border-slate-100 pt-5 text-sm text-slate-500">Hồ sơ Employee: {{ $user->employee ? 'Đã liên kết' : 'Chưa liên kết' }}</div></div><section class="app-panel mt-6 max-w-3xl p-6">
<h2 class="app-heading">Đặt lại mật khẩu</h2>
<p class="app-subtitle">Gửi liên kết xác thực đến {{ $user->email }}. Chủ tài khoản tự đặt mật khẩu mới qua email.</p>
<form method="POST" action="{{ route('admin.users.reset-password', $user) }}" class="mt-4">@csrf
<button class="app-button-primary" type="submit">Gửi email đặt lại mật khẩu</button>
<x-input-error :messages="$errors->get('email')" class="mt-2" />
</form></section>@endsection
