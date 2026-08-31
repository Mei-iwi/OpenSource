@extends('layouts.app')
@section('title', 'Phòng ban')
@section('content')
<x-page-header eyebrow="HR / Danh mục" title="Phòng ban" description="Quản lý đơn vị và phân bổ nhân sự."><button class="rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white">Thêm phòng ban</button></x-page-header>
<div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"><div class="mb-5 flex items-center justify-between"><input type="search" placeholder="Tìm phòng ban..." class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm sm:max-w-sm"><span class="text-sm text-slate-500">0 phòng ban</span></div><x-empty-state title="Chưa có phòng ban" /></div>
@endsection
