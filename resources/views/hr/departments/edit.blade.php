@extends('layouts.app')
@section('title', 'Sửa phòng ban')
@section('content')<x-page-header eyebrow="HR / Danh mục" title="Cập nhật phòng ban" description="Chỉnh sửa thông tin đơn vị." /><div class="max-w-2xl rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"><form method="POST" action="{{ route('hr.departments.update', $department) }}">@method('PUT') @include('hr.departments._form')</form></div>@endsection
