@extends('layouts.app')
@section('title', 'Tạo nhân viên và tài khoản')
@section('content')<x-page-header eyebrow="HR / Nhân sự" title="Tạo nhân viên và tài khoản" description="Một lần nhập thông tin để tạo hồ sơ nhân viên và tài khoản đăng nhập. Mã được sinh tự động theo vai trò." /><div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"><form id="employee-form" method="POST" action="{{ route('hr.employees.store') }}" enctype="multipart/form-data">@include('hr.employees._form')</form></div>@endsection
