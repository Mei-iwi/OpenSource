@extends('layouts.app')
@section('title', 'Tạo chức vụ')
@section('content')
<x-page-header eyebrow="HR / Danh mục" title="Tạo chức vụ" description="Khai báo một chức danh / vị trí công việc mới trong hệ thống." />
<div class="max-w-2xl rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
    <form method="POST" action="{{ route('hr.job-positions.store') }}">
        @include('hr.job_positions._form')
    </form>
</div>
@endsection
