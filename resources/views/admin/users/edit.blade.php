@extends('layouts.app')
@section('title', 'Sửa tài khoản')
@section('content')<x-page-header eyebrow="Admin / Tài khoản" title="Cập nhật tài khoản" description="Cập nhật thông tin và vai trò hợp lệ của user." /><div class="max-w-3xl rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"><form method="POST" action="{{ route('admin.users.update', $user) }}">@method('PUT') @include('admin.users._form')</form></div>@endsection
