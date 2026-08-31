@extends('layouts.app')
@section('title', 'Tạo phòng ban')
@section('content')<x-page-header eyebrow="HR / Danh mục" title="Tạo phòng ban" description="Khai báo một đơn vị mới trong hệ thống." /><div class="max-w-2xl rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"><form method="POST" action="{{ route('hr.departments.store') }}">@include('hr.departments._form')</form></div>@endsection
