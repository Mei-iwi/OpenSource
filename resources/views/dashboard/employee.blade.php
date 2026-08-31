@extends('layouts.app')
@section('title', 'Tổng quan nhân viên')
@section('content')
<x-page-header eyebrow="Khu vực nhân viên" title="Xin chào, nhân viên" description="Xem nhanh hồ sơ và lịch sử chấm công cá nhân của bạn."><a href="#profile" class="rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700">Hồ sơ của tôi</a></x-page-header>
<div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
    @foreach ([['Ngày công tháng này','20','Ngày làm việc','text-indigo-600'],['Có mặt đúng giờ','18','90% tổng ngày công','text-emerald-600'],['Ngày nghỉ','02','Theo lịch đã duyệt','text-amber-600']] as $card)
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><p class="text-sm text-slate-500">{{ $card[0] }}</p><p class="mt-3 text-3xl font-bold {{ $card[3] }}">{{ $card[1] }}</p><p class="mt-2 text-xs text-slate-400">{{ $card[2] }}</p></div>
    @endforeach
</div>
<div id="profile" class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"><h2 class="font-semibold">Hồ sơ cá nhân</h2><div class="mt-5 grid gap-4 sm:grid-cols-2"><div><p class="text-xs text-slate-500">Họ và tên</p><p class="mt-1 font-medium">Chưa kết nối tài khoản</p></div><div><p class="text-xs text-slate-500">Mã nhân viên</p><p class="mt-1 font-medium">—</p></div></div></div>
@endsection
