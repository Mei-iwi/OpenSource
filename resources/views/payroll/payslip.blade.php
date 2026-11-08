@extends('layouts.app')
@section('title', 'Phiếu lương')
@section('content')
<x-page-header eyebrow="{{ $isHr ? 'HR / Lương' : 'Cá nhân' }}" title="Phiếu lương tháng {{ $payslip->period->period_month->format('m/Y') }}" description="{{ $payslip->employee_code }} · {{ $payslip->employee_name }}">
    <button type="button" onclick="window.print()" class="app-button-secondary print:hidden">In phiếu</button>
</x-page-header>
<section class="app-panel max-w-3xl p-6 sm:p-8">
    <p class="font-semibold">{{ $payslip->employee_code }} · {{ $payslip->employee_name }}</p>
    <p class="app-subtitle">{{ $payslip->period->status === 'finalized' ? 'Đã chốt' : 'Bản nháp — chỉ HR/Admin xem' }}</p>
    <dl class="mt-5 grid gap-4 sm:grid-cols-2">
        @foreach([
            'Ngày làm việc theo lịch' => $payslip->scheduled_days,
            'Ngày đã làm' => $payslip->worked_days,
            'Ngày nghỉ hưởng lương' => $payslip->paid_leave_days,
            'Ngày nghỉ không lương' => $payslip->unpaid_leave_days,
            'Ngày không được trả lương' => $payslip->unpaid_days,
            'Lương cơ bản tháng' => $payslip->base_salary,
            'Lương cơ bản theo ngày công' => $payslip->base_pay,
            'Giảm do ngày không hưởng lương' => $payslip->attendance_deduction,
            'Phụ cấp cố định' => $payslip->allowance,
            'Tổng trước khấu trừ cố định' => $payslip->gross_pay,
            'Khấu trừ cố định' => $payslip->fixed_deduction,
        ] as $label => $value)
            <div class="border-b pb-2"><dt class="app-label">{{ $label }}</dt><dd class="mt-1 font-medium">{{ $value }}</dd></div>
        @endforeach
    </dl>
    <p class="mt-6 text-lg font-bold">Thực lĩnh: {{ $payslip->net_pay }} {{ config('payroll.currency') }}</p>
</section>
@endsection
