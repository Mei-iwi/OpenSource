@extends('layouts.app')
@section('title', 'Sửa nhân viên')
@section('content')<x-page-header eyebrow="HR / Nhân sự" title="Cập nhật nhân viên" description="Cập nhật thông tin tài khoản và hồ sơ trong một transaction." /><div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"><form method="POST" action="{{ route('hr.employees.update', $employee) }}">@method('PUT') @include('hr.employees._form')</form></div>@endsection
