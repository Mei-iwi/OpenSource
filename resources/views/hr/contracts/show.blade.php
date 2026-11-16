@extends('layouts.app')
@section('title', 'Hợp đồng · '.$employee->user->name)
@section('content')
<x-page-header eyebrow="HR / Hợp đồng" title="{{ $employee->user->name }}" description="{{ $employee->employee_code }} · {{ $employee->department->name }}">
    <a class="app-button-secondary" href="{{ route('hr.contracts.index') }}">Danh sách hợp đồng</a>
    <a class="app-button-primary" href="{{ route('hr.contracts.edit', $employee) }}">Cập nhật</a>
</x-page-header>
@include('hr.contracts._details')
@endsection
