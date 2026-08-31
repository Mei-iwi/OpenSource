@extends('layouts.app')
@section('title', 'Chấm công')
@section('content')
<x-page-header eyebrow="HR / Vận hành" title="Chấm công" description="Theo dõi trạng thái làm việc theo ngày."><button class="rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white">Ghi nhận chấm công</button></x-page-header>
<div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"><div class="mb-5 grid gap-3 sm:grid-cols-3"><input type="date" class="rounded-lg border border-slate-300 px-3 py-2 text-sm"><select class="rounded-lg border border-slate-300 px-3 py-2 text-sm"><option>Tất cả trạng thái</option></select><input type="search" placeholder="Tìm nhân viên..." class="rounded-lg border border-slate-300 px-3 py-2 text-sm"></div><x-empty-state title="Chưa có bản ghi chấm công" /></div>
@endsection
