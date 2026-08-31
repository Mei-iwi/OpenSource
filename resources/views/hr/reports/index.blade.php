@extends('layouts.app')
@section('title', 'Báo cáo')
@section('content')
<x-page-header eyebrow="HR / Phân tích" title="Báo cáo & thống kê" description="Khung trình bày báo cáo nhân sự theo kỳ và phòng ban."><button class="rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700">Xuất CSV</button></x-page-header>
<div id="reports" class="grid gap-6 lg:grid-cols-3"><div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm lg:col-span-2"><h2 class="font-semibold">Tổng quan chấm công</h2><div class="mt-5"><x-empty-state title="Chưa có dữ liệu báo cáo" /></div></div><div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"><h2 class="font-semibold">Bộ lọc báo cáo</h2><div class="mt-5 space-y-4"><label class="block text-sm font-medium">Tháng<select class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2"><option>Tháng hiện tại</option></select></label><label class="block text-sm font-medium">Phòng ban<select class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2"><option>Tất cả phòng ban</option></select></label></div></div></div>
@endsection
