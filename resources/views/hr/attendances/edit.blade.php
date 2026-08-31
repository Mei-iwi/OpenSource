@extends('layouts.app')
@section('title', 'Sửa chấm công')
@section('content')<x-page-header eyebrow="HR / Vận hành" title="Sửa chấm công" description="Cập nhật bản ghi đã chọn." />
<form method="POST" action="{{ route('hr.attendances.update', $attendance) }}" class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">@method('PUT')@include('hr.attendances._form')</form>@endsection
