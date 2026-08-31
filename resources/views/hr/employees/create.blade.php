@extends('layouts.app')
@section('title', 'Tạo nhân viên')
@section('content')<x-page-header eyebrow="HR / Nhân sự" title="Tạo nhân viên" description="Tạo đồng thời tài khoản Employee và hồ sơ nhân sự." /><div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"><form method="POST" action="{{ route('hr.employees.store') }}">@include('hr.employees._form')</form></div>@endsection
