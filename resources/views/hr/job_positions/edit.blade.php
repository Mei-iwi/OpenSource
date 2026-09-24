@extends('layouts.app')
@section('title', 'Cập nhật chức vụ')
@section('content')
<x-page-header eyebrow="HR / Danh mục" title="Cập nhật chức vụ" description="Chỉnh sửa thông tin vị trí công việc." />
<div class="max-w-2xl rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
    <form method="POST" action="{{ route('hr.job-positions.update', $jobPosition) }}">
        @method('PUT')
        @include('hr.job_positions._form')
    </form>
</div>
@endsection
