@extends('layouts.app')
@section('title', 'Kỳ lương')
@section('content')
<x-page-header eyebrow="HR / Lương" title="Kỳ lương {{ $period->period_month->format('m/Y') }}" description="{{ $period->status === 'finalized' ? 'Đã chốt' : 'Bản nháp' }}">
    <a href="{{ route('hr.payroll.index') }}" class="app-button-secondary">Các kỳ lương</a>
</x-page-header>
<div class="app-panel p-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <p><span class="font-semibold">{{ $period->payslips->count() }}</span> phiếu · Tổng thực lĩnh <span class="font-bold">{{ $totalNet }} {{ config('payroll.currency') }}</span></p>
        @if($period->status === 'draft' && $period->payslips->isNotEmpty())
            <form method="POST" action="{{ route('hr.payroll.finalize', $period) }}">@csrf<button class="app-button-primary">Chốt kỳ lương</button></form>
        @endif
    </div>
    <x-input-error :messages="$errors->get('payroll')" />
    @if($period->finalized_at)<p class="app-subtitle mt-2">Chốt lúc {{ $period->finalized_at->format('d/m/Y H:i') }} bởi {{ $period->finalizer?->name ?? 'Tài khoản đã xóa' }}</p>@endif
    <div class="mt-5 overflow-x-auto"><table class="w-full text-left text-sm"><thead><tr class="border-b"><th class="py-2">Nhân viên</th><th>Ngày công</th><th>Nghỉ hưởng lương</th><th>Nghỉ không lương</th><th>Thực lĩnh</th><th></th></tr></thead><tbody>
        @foreach($period->payslips as $payslip)
            <tr class="border-b"><td class="py-3">{{ $payslip->employee_code }} · {{ $payslip->employee_name }}</td><td>{{ $payslip->worked_days }} / {{ $payslip->scheduled_days }}</td><td>{{ $payslip->paid_leave_days }}</td><td>{{ $payslip->unpaid_leave_days }}</td><td>{{ $payslip->net_pay }}</td><td><a class="font-semibold text-indigo-600 underline" href="{{ route('hr.payroll.payslip', [$period, $payslip]) }}">Phiếu lương</a></td></tr>
        @endforeach
    </tbody></table></div>
</div>
@endsection
