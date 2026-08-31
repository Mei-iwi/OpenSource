@extends('layouts.app')
@section('title', 'Lịch sử chấm công')
@section('content')
<x-page-header eyebrow="Employee / Cá nhân" title="Lịch sử chấm công" description="Chỉ hiển thị dữ liệu chấm công của tài khoản đang đăng nhập."><span class="rounded-lg bg-emerald-50 px-3 py-2 text-sm font-semibold text-emerald-700">Tháng hiện tại</span></x-page-header>
<div id="attendance" class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"><div class="mb-5 flex flex-col gap-3 sm:flex-row"><label class="sr-only" for="month">Chọn tháng</label><input id="month" type="month" class="rounded-lg border border-slate-300 px-3 py-2 text-sm"><span class="text-sm text-slate-500 sm:py-2">Tổng: 0 ngày</span></div><div class="overflow-x-auto"><table class="min-w-full text-left text-sm"><thead class="border-b border-slate-200 text-xs uppercase text-slate-500"><tr><th class="px-3 py-3">Ngày</th><th class="px-3 py-3">Check-in</th><th class="px-3 py-3">Check-out</th><th class="px-3 py-3">Trạng thái</th></tr></thead><tbody><tr><td colspan="4" class="px-3 py-3"><x-empty-state title="Chưa có lịch sử chấm công" /></td></tr></tbody></table></div></div>
@endsection
