@extends('layouts.app')
@section('title', 'Dashboard Admin')
@section('content')
<x-page-header eyebrow="Admin workspace" title="Tổng quan hệ thống" description="Theo dõi tài khoản, vai trò và sức khỏe vận hành của website nhân sự."><a href="#users" class="rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700">Quản lý tài khoản</a></x-page-header>
<div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
    @foreach ([['Tổng tài khoản','128','+8% so với tháng trước','text-indigo-600'],['Tài khoản HR','12','Đang hoạt động','text-violet-600'],['Nhân viên','116','Đang hoạt động','text-emerald-600'],['Chờ xử lý','04','Cần kiểm tra','text-amber-600']] as $card)
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><p class="text-sm text-slate-500">{{ $card[0] }}</p><p class="mt-3 text-3xl font-bold {{ $card[3] }}">{{ $card[1] }}</p><p class="mt-2 text-xs text-slate-400">{{ $card[2] }}</p></div>
    @endforeach
</div>
<div id="users" class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"><div class="flex items-center justify-between"><div><h2 class="font-semibold text-slate-900">Tài khoản & vai trò</h2><p class="mt-1 text-sm text-slate-500">Khung danh sách tài khoản dành cho Admin.</p></div><x-status-badge status="admin" label="Admin only" /></div><div class="mt-5"><x-empty-state title="Chưa có dữ liệu tài khoản" description="Bảng tài khoản sẽ được nối với authentication ở milestone tiếp theo." /></div></div>
@endsection
