@extends('layouts.app')
@section('title', 'Cập nhật hợp đồng · '.$employee->user->name)
@section('content')
<x-page-header eyebrow="HR / Hợp đồng" title="Cập nhật hợp đồng" description="{{ $employee->user->name }} · {{ $employee->employee_code }}" />
<form class="app-panel max-w-4xl p-6 sm:p-8" method="POST" action="{{ route('hr.contracts.update', $employee) }}">
    @csrf @method('PUT')
    @include('hr.contracts._fields')
    <div class="mt-7 flex flex-wrap gap-3">
        <button class="app-button-primary" type="submit">Lưu hợp đồng</button>
        <a class="app-button-secondary" href="{{ route('hr.contracts.show', $employee) }}">Hủy</a>
    </div>
</form>
@endsection
