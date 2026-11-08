@extends('layouts.app')
@section('title', 'Cấu hình lương nhân viên')
@section('content')
<x-page-header eyebrow="HR / Lương" title="Cấu hình lương" description="{{ $employee->employee_code }} · {{ $employee->user->name }}" />
<form method="POST" action="{{ route('hr.payroll.salaries.update', $employee) }}" class="app-panel max-w-xl space-y-5 p-6">
    @csrf @method('PUT')
    @foreach(['base_salary' => 'Lương cơ bản tháng', 'allowance' => 'Phụ cấp cố định tháng', 'fixed_deduction' => 'Khấu trừ cố định tháng'] as $field => $label)
        <div><label for="{{ $field }}" class="app-label">{{ $label }}</label><input id="{{ $field }}" name="{{ $field }}" inputmode="decimal" required class="app-input w-full" value="{{ old($field, $employee->salary?->$field ?? '0.00') }}"><x-input-error :messages="$errors->get($field)" /></div>
    @endforeach
    <p class="app-subtitle">Đơn vị: {{ config('payroll.currency') }}. Nhập số không âm, tối đa hai chữ số thập phân. Khấu trừ này không tự tính thuế hay bảo hiểm.</p>
    <button class="app-button-primary">Lưu cấu hình</button>
</form>
@endsection
