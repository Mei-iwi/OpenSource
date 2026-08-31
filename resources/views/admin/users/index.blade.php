@extends('layouts.app')
@section('title', 'Tài khoản')
@section('content')
<x-page-header eyebrow="Admin" title="Tài khoản hệ thống" description="Khung quản lý tài khoản và vai trò người dùng."><button class="rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white">Thêm tài khoản</button></x-page-header>
<div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"><div class="mb-5 flex flex-col gap-3 sm:flex-row"><label class="sr-only" for="user-search">Tìm tài khoản</label><input id="user-search" type="search" placeholder="Tìm theo tên hoặc email..." class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm sm:max-w-sm"><select class="rounded-lg border border-slate-300 px-3 py-2 text-sm"><option>Tất cả vai trò</option><option>Admin</option><option>HR</option><option>Employee</option></select></div><x-empty-state title="Chưa có tài khoản để hiển thị" /></div>
@endsection
