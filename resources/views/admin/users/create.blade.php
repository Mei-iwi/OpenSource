@extends('layouts.app')
@section('title', 'Tạo tài khoản')
@section('content')<x-page-header eyebrow="Admin / Tài khoản" title="Tạo tài khoản" description="Chỉ có thể tạo tài khoản với vai trò HR hoặc Employee." /><div class="max-w-3xl rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"><form method="POST" action="{{ route('admin.users.store') }}">@include('admin.users._form')</form></div>@endsection
