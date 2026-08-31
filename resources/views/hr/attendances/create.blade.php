@extends('layouts.app')
@section('title', 'Ghi nhận chấm công')
@section('content')<x-page-header eyebrow="HR / Vận hành" title="Ghi nhận chấm công" description="Tạo một bản ghi cho nhân viên." />
<form method="POST" action="{{ route('hr.attendances.store') }}" class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">@include('hr.attendances._form')</form>@endsection
