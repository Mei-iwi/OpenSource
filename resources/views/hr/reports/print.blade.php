@extends('layouts.app')
@section('title', 'In báo cáo')
@push('styles')
<style>
@media print {
    @page { size: A4; margin: 12mm; }
    aside, nav, header, button, a, .top-nav, .bottom-nav, #company-advertisement, .logout-dialog { display: none !important; }
    html, body, .app-shell, .app-body, .app-workspace, .app-main { display: block !important; height: auto !important; min-height: 0 !important; overflow: visible !important; background: white !important; color: black !important; }
    .app-main { padding: 0 !important; }
    .print-report { box-shadow: none; border: 0; padding: 0; background: white !important; color: black !important; }
    .print-report .text-slate-500 { color: #475569 !important; }
    thead { display: table-header-group; }
    tr { break-inside: avoid; }
}
</style>
@endpush
@section('content')
<div class="print-report rounded-2xl border border-slate-200 bg-white p-6">
    <div class="mb-6 grid items-center gap-3 sm:grid-cols-3">
        <p class="text-sm text-slate-500">Ngày in: {{ now()->format('d/m/Y H:i') }}</p>
        <h1 class="text-center text-2xl font-bold">Báo cáo chấm công</h1>
        <div class="text-right"><button onclick="window.print()" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white">In báo cáo</button></div>
    </div>
    <div class="mb-5 grid grid-cols-5 gap-3">
        @foreach(['Tổng' => $totalRecords, 'Có mặt' => $counts['present'] ?? 0, 'Đi muộn' => $counts['late'] ?? 0, 'Vắng' => $counts['absent'] ?? 0, 'Nghỉ phép' => $counts['leave'] ?? 0] as $label => $value)
            <div class="rounded-lg bg-slate-50 p-3"><p class="text-xs text-slate-500">{{ $label }}</p><p class="text-xl font-bold">{{ $value }}</p></div>
        @endforeach
    </div>
    <table class="min-w-full text-left text-sm">
        <thead class="border-b"><tr><th class="px-2 py-2">Ngày</th><th class="px-2 py-2">Nhân viên</th><th class="px-2 py-2">Phòng ban</th><th class="px-2 py-2">Trạng thái</th></tr></thead>
        <tbody>
            @forelse($attendances as $attendance)
                <tr class="border-b"><td class="px-2 py-2">{{ $attendance->work_date->format('d/m/Y') }}</td><td class="px-2 py-2">{{ $attendance->employee->employee_code }} - {{ $attendance->employee->user->name }}</td><td class="px-2 py-2">{{ $attendance->employee->department->name }}</td><td class="px-2 py-2">{{ ['present' => 'Có mặt', 'late' => 'Đi muộn', 'absent' => 'Vắng', 'leave' => 'Nghỉ phép'][$attendance->status] ?? $attendance->status }}</td></tr>
            @empty
                <tr><td colspan="4" class="px-2 py-4 text-center">Không có dữ liệu phù hợp.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
