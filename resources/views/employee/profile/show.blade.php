@extends('layouts.app')
@section('title', 'Hồ sơ cá nhân')
@section('content')
<x-page-header eyebrow="Employee / Cá nhân" title="Hồ sơ của tôi" description="Thông tin cá nhân sẽ được kết nối sau khi có authentication."><button class="rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white">Chỉnh sửa hồ sơ</button></x-page-header>
<div id="profile" class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"><div class="flex items-center gap-4 border-b border-slate-100 pb-6"><div class="flex h-16 w-16 items-center justify-center rounded-full bg-indigo-100 text-xl font-bold text-indigo-700">NV</div><div><h2 class="font-semibold">Chưa kết nối tài khoản</h2><p class="text-sm text-slate-500">Mã nhân viên: —</p></div></div><div class="grid gap-5 pt-6 sm:grid-cols-2 lg:grid-cols-3"><div><p class="text-xs text-slate-500">Email</p><p class="mt-1 text-sm">—</p></div><div><p class="text-xs text-slate-500">Phòng ban</p><p class="mt-1 text-sm">—</p></div><div><p class="text-xs text-slate-500">Chức vụ</p><p class="mt-1 text-sm">—</p></div></div></div>
@endsection
