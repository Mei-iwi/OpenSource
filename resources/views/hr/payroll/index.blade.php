@extends('layouts.app')
@section('title', 'Bảng lương')
@section('content')
<x-page-header eyebrow="HR / Lương" title="Bảng lương" description="Tạo kỳ lương nháp, kiểm tra phiếu lương rồi chốt kỳ.">
    <a href="{{ route('hr.payroll.salaries.index') }}" class="app-button-secondary">Cấu hình lương</a>
</x-page-header>
<div class="app-panel p-6">
    <form method="POST" action="{{ route('hr.payroll.generate') }}" class="flex flex-wrap items-end gap-3">
        @csrf
        <div><label for="month" class="app-label">Tháng lương</label><input id="month" name="month" type="month" required class="app-input" value="{{ old('month', now()->subMonth()->format('Y-m')) }}"><x-input-error :messages="$errors->get('month')" /></div>
        <button class="app-button-primary">Tạo / tạo lại kỳ nháp</button>
    </form>
</div>
<div class="app-panel mt-6 overflow-x-auto p-6">
    <h2 class="font-semibold">Các kỳ lương</h2>
    <table class="mt-4 w-full text-left text-sm"><thead><tr class="border-b"><th class="py-2">Tháng</th><th>Trạng thái</th><th>Phiếu lương</th><th></th></tr></thead><tbody>
        @forelse($periods as $period)
            <tr class="border-b"><td class="py-3">{{ $period->period_month->format('m/Y') }}</td><td>{{ $period->status === 'finalized' ? 'Đã chốt' : 'Nháp' }}</td><td>{{ $period->payslips_count }}</td><td><a class="font-semibold text-indigo-600 underline" href="{{ route('hr.payroll.show', $period) }}">Xem</a></td></tr>
        @empty
            <tr><td colspan="4" class="py-4 text-[var(--app-muted)]">Chưa có kỳ lương.</td></tr>
        @endforelse
    </tbody></table>
    <div class="mt-4">{{ $periods->links() }}</div>
</div>
@endsection
