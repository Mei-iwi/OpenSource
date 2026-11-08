@extends('layouts.app')
@section('title', 'Phiếu lương của tôi')
@section('content')
<x-page-header eyebrow="Cá nhân" title="Phiếu lương của tôi" description="Các kỳ lương đã chốt." />
<div class="app-panel p-6"><div class="space-y-3">
    @forelse($payslips as $payslip)
        <a href="{{ route('me.payslips.show', $payslip) }}" class="flex items-center justify-between border-b py-3"><span>Tháng {{ $payslip->period->period_month->format('m/Y') }}</span><span class="font-bold">{{ $payslip->net_pay }}</span></a>
    @empty
        <p class="app-subtitle">Chưa có phiếu lương đã chốt.</p>
    @endforelse
</div><div class="mt-4">{{ $payslips->links() }}</div></div>
@endsection
