@extends('layouts.app')
@section('title', 'Tổng quan quản trị')
@section('content')
<x-page-header eyebrow="Khu vực quản trị" title="Tổng quan hệ thống" description="Theo dõi nhanh tình hình nhân sự và chấm công.">
    <a href="{{ route('admin.users.index') }}" class="app-button-primary">Quản lý tài khoản</a>
</x-page-header>

<form method="GET" class="app-panel mb-6 flex flex-wrap items-end gap-3 p-4" aria-label="Bộ lọc dashboard">
    <div><label for="admin-month" class="app-label">Tháng</label><select id="admin-month" name="month" class="app-input mt-1"><option value="">Tháng hiện tại</option>@foreach(range(1, 12) as $month)<option value="{{ $month }}" @selected($selectedMonth === $month)>{{ $month }}</option>@endforeach</select></div>
    <div><label for="admin-year" class="app-label">Năm</label><select id="admin-year" name="year" class="app-input mt-1">@foreach(range(now()->year - 2, now()->year + 1) as $year)<option value="{{ $year }}" @selected($selectedYear === $year)>{{ $year }}</option>@endforeach</select></div>
    <button class="app-button-primary" type="submit">Lọc dữ liệu</button><a href="{{ route('admin.dashboard') }}" class="app-button-secondary">Đặt lại</a>
</form>

@php($kpis = [
    ['Tổng nhân viên', $totalEmployees, 'text-orange-600', 'Nhân sự hiện có'],
    ['Đang làm việc', $activeEmployees, 'text-emerald-600', 'Trạng thái active'],
    ['Ngừng làm việc', $inactiveEmployees, 'text-slate-500', 'Trạng thái inactive'],
    ['Có mặt hôm nay', $presentToday, 'text-sky-600', 'Chấm công hôm nay'],
    ['Đi muộn hôm nay', $lateToday, 'text-amber-600', 'Chấm công hôm nay'],
    ['Vắng hôm nay', $absentToday, 'text-red-600', 'Chấm công hôm nay'],
])
<div class="grid gap-4 sm:grid-cols-2 md:grid-cols-3">@foreach($kpis as $kpi)<div class="app-panel flex min-h-32 items-start justify-between p-5"><div><p class="text-sm font-medium text-[var(--app-muted)]">{{ $kpi[0] }}</p><p class="mt-3 text-3xl font-bold {{ $kpi[2] }}">{{ $kpi[1] }}</p><p class="mt-1 text-xs text-[var(--app-muted)]">{{ $kpi[3] }}</p></div><span class="rounded-xl bg-orange-50 px-2.5 py-2 text-orange-600" aria-hidden="true">◆</span></div>@endforeach</div>
<div class="app-panel mt-6 flex flex-wrap items-center justify-between gap-4 p-5"><div><h2 class="app-heading">Đơn nghỉ chờ duyệt</h2><p class="app-subtitle">{{ $pendingLeaveRequests->count() }} đơn gần nhất cần xem xét</p></div><a href="{{ route('hr.leave-requests.index', ['status' => 'pending']) }}" class="app-button-secondary">Mở danh sách đơn nghỉ</a></div>

<div class="dashboard-two-column mt-6 grid gap-6">
    <div class="app-panel p-5 sm:p-6"><div class="flex items-start justify-between"><div><h2 class="app-heading">Tình trạng chấm công tháng {{ sprintf('%02d/%d', $selectedMonth, $selectedYear) }}</h2><p class="app-subtitle">Tổng hợp theo trạng thái</p></div><span class="app-badge app-badge-info">Thống kê</span></div><div class="relative mt-5 h-72">@if($periodStatus->sum())<canvas id="attendance-status-chart" aria-label="Biểu đồ tình trạng chấm công"></canvas>@else<x-empty-state title="Chưa có dữ liệu thống kê trong khoảng thời gian này." /></div>@endif</div>
    <div class="app-panel p-5 sm:p-6"><div><h2 class="app-heading">Nhân sự theo phòng ban</h2><p class="app-subtitle">Phân bổ nhân viên hiện tại</p></div><div class="relative mt-5 h-72">@if($employeesByDepartment->count())<canvas id="department-chart" aria-label="Biểu đồ nhân sự theo phòng ban"></canvas>@else<x-empty-state title="Chưa có dữ liệu thống kê trong khoảng thời gian này." /></div>@endif</div>
</div>
<div class="dashboard-two-column mt-6 grid gap-6">
    <div class="app-panel min-w-0 p-5 sm:p-6"><div><h2 class="app-heading">Xu hướng chuyên cần 6 tháng gần nhất</h2><p class="app-subtitle">Số bản ghi theo trạng thái</p></div><div class="relative mt-5 h-72"><canvas id="attendance-trend-chart" aria-label="Biểu đồ xu hướng chuyên cần"></canvas></div></div>
    <div class="app-panel p-5 sm:p-6"><h2 class="app-heading">Tài khoản theo role</h2><div class="mt-5 space-y-3">@foreach(['admin'=>'Quản trị viên','hr'=>'Nhân sự','employee'=>'Nhân viên'] as $role => $label)<div class="flex items-center justify-between border-b border-[var(--app-border)] pb-3"><span class="text-sm text-[var(--app-muted)]">{{ $label }}</span><span class="font-bold text-[var(--app-text)]">{{ $usersByRole[$role] ?? 0 }}</span></div>@endforeach</div><p class="mt-5 text-sm text-[var(--app-muted)]">Module HR</p><a href="{{ route('hr.reports.index') }}" class="mt-2 inline-flex text-sm font-semibold text-orange-600 hover:text-orange-700">Mở báo cáo HR →</a></div>
</div>
<div class="app-panel mt-6 overflow-hidden"><div class="flex items-center justify-between border-b border-[var(--app-border)] p-5"><div><h2 class="app-heading">Chấm công gần đây</h2><p class="app-subtitle">Các bản ghi mới nhất trong hệ thống</p></div><a href="{{ route('hr.attendances.index') }}" class="text-sm font-semibold text-orange-600">Xem tất cả</a></div><div class="overflow-x-auto"><table class="app-table"><thead><tr><th>Nhân viên</th><th>Phòng ban</th><th>Ngày</th><th>Trạng thái</th></tr></thead><tbody>@forelse($recentAttendance as $attendance)<tr><td class="font-medium">{{ $attendance->employee?->user?->name ?? '—' }}</td><td>{{ $attendance->employee?->department?->name ?? '—' }}</td><td>{{ $attendance->work_date?->format('d/m/Y') ?? '—' }}</td><td><x-status-badge :status="$attendance->status" /></td></tr>@empty<tr><td colspan="4" class="py-10 text-center text-[var(--app-muted)]">Chưa có dữ liệu chấm công.</td></tr>@endforelse</tbody></table></div></div>
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => window.renderDashboardCharts({
    attendanceStatus: { labels: ['Có mặt', 'Đi muộn', 'Vắng mặt', 'Nghỉ phép'], values: [{{ $periodStatus['present'] ?? 0 }}, {{ $periodStatus['late'] ?? 0 }}, {{ $periodStatus['absent'] ?? 0 }}, {{ $periodStatus['leave'] ?? 0 }}] },
    departments: { labels: @json($employeesByDepartment->pluck('name')->values()), values: @json($employeesByDepartment->pluck('total')->map(fn ($value) => (int) $value)->values()) },
    trend: @json($trend),
}));
</script>
@endpush
@endsection
