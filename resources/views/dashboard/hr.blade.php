@extends('layouts.app')
@section('title', 'Dashboard HR')
@section('content')
<x-page-header eyebrow="HR workspace" title="Dashboard nhân sự" description="Một góc nhìn nhanh về nhân viên, phòng ban và chấm công."><a href="#employees" class="rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700">Xem nhân viên</a></x-page-header>
<div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
    @foreach ([['Tổng nhân viên','116','Toàn hệ thống','text-indigo-600'],['Phòng ban','08','Đang quản lý','text-violet-600'],['Có mặt hôm nay','104','89.6% nhân sự','text-emerald-600'],['Vắng mặt','12','Cần theo dõi','text-amber-600']] as $card)
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><p class="text-sm text-slate-500">{{ $card[0] }}</p><p class="mt-3 text-3xl font-bold {{ $card[3] }}">{{ $card[1] }}</p><p class="mt-2 text-xs text-slate-400">{{ $card[2] }}</p></div>
    @endforeach
</div>
<div class="mt-6 grid gap-6 xl:grid-cols-2">
    <div id="departments" class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"><h2 class="font-semibold">Phòng ban</h2><p class="mt-1 text-sm text-slate-500">Phân bổ nhân sự theo đơn vị.</p><div class="mt-5"><x-empty-state title="Chưa có dữ liệu phòng ban" /></div></div>
    <div id="attendances" class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"><h2 class="font-semibold">Chấm công hôm nay</h2><p class="mt-1 text-sm text-slate-500">Tóm tắt trạng thái chấm công.</p><div class="mt-5"><x-empty-state title="Chưa có dữ liệu chấm công" /></div></div>
</div>
@endsection
