@extends('layouts.app')
@section('title', 'Tổng quan Quản trị')
@section('content')
<x-page-header eyebrow="Khu vực Quản trị tối cao" title="Bảng điều khiển hệ thống" description="Theo dõi toàn diện tình hình nhân sự, chuyên cần và hoạt động vận hành thời gian thực.">
    <a href="{{ route('admin.users.index') }}" class="app-button-primary">
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" x2="19" y1="8" y2="14"/><line x1="22" x2="16" y1="11" y2="11"/></svg>
        <span>Quản lý tài khoản</span>
    </a>
</x-page-header>

<!-- Filter Toolbar -->
<form method="GET" class="app-panel mb-6 flex flex-wrap items-center justify-between gap-4 p-4" aria-label="Bộ lọc dashboard">
    <div class="flex flex-wrap items-center gap-3">
        <div class="flex items-center gap-2">
            <span class="text-xs font-bold uppercase tracking-wider text-[var(--app-muted)]">Thời gian:</span>
            <select id="admin-month" name="month" class="app-input text-xs font-semibold py-2">
                <option value="">Tháng hiện tại</option>
                @foreach(range(1, 12) as $month)
                    <option value="{{ $month }}" @selected($selectedMonth === $month)>Tháng {{ $month }}</option>
                @endforeach
            </select>
            <select id="admin-year" name="year" class="app-input text-xs font-semibold py-2">
                @foreach(range(now()->year - 2, now()->year + 1) as $year)
                    <option value="{{ $year }}" @selected($selectedYear === $year)>Năm {{ $year }}</option>
                @endforeach
            </select>
        </div>
        <button class="app-button-primary text-xs py-2 px-3.5" type="submit">
            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
            <span>Áp dụng lọc</span>
        </button>
        <a href="{{ route('admin.dashboard') }}" class="app-button-secondary text-xs py-2 px-3.5">
            Đặt lại
        </a>
    </div>

    <div class="flex items-center gap-2">
        <span class="text-xs text-[var(--app-muted)]">Kỳ báo cáo:</span>
        <span class="rounded-lg bg-indigo-500/10 px-2.5 py-1 text-xs font-bold text-indigo-600 dark:text-indigo-400 border border-indigo-500/20">
            {{ sprintf('%02d/%d', $selectedMonth, $selectedYear) }}
        </span>
    </div>
</form>

<!-- Bento Grid: Top 6 KPI Metric Cards -->
<div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
    <!-- Total Employees -->
    <div class="kpi-card flex flex-col justify-between">
        <div class="flex items-center justify-between">
            <span class="text-xs font-bold text-[var(--app-muted)]">Tổng nhân sự</span>
            <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-500/15 text-indigo-600 dark:text-indigo-400">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </div>
        </div>
        <div class="mt-4">
            <p class="text-3xl font-extrabold tracking-tight text-[var(--app-text)]">{{ $totalEmployees }}</p>
            <p class="mt-1 flex items-center gap-1 text-xs font-medium text-emerald-600 dark:text-emerald-400">
                <span>↑ Quy mô công ty</span>
            </p>
        </div>
    </div>

    <!-- Active Employees -->
    <div class="kpi-card flex flex-col justify-between">
        <div class="flex items-center justify-between">
            <span class="text-xs font-bold text-[var(--app-muted)]">Đang làm việc</span>
            <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-500/15 text-emerald-600 dark:text-emerald-400">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            </div>
        </div>
        <div class="mt-4">
            <p class="text-3xl font-extrabold tracking-tight text-emerald-600 dark:text-emerald-400">{{ $activeEmployees }}</p>
            <p class="mt-1 text-xs font-medium text-[var(--app-muted)]">Hợp đồng hiệu lực</p>
        </div>
    </div>

    <!-- Inactive Employees -->
    <div class="kpi-card flex flex-col justify-between">
        <div class="flex items-center justify-between">
            <span class="text-xs font-bold text-[var(--app-muted)]">Đã nghỉ việc</span>
            <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-slate-500/15 text-slate-600 dark:text-slate-400">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="4.93" x2="19.07" y1="4.93" y2="19.07"/></svg>
            </div>
        </div>
        <div class="mt-4">
            <p class="text-3xl font-extrabold tracking-tight text-slate-500 dark:text-slate-400">{{ $inactiveEmployees }}</p>
            <p class="mt-1 text-xs font-medium text-[var(--app-muted)]">Hồ sơ đã đóng</p>
        </div>
    </div>

    <!-- Present Today -->
    <div class="kpi-card flex flex-col justify-between">
        <div class="flex items-center justify-between">
            <span class="text-xs font-bold text-[var(--app-muted)]">Có mặt hôm nay</span>
            <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-sky-500/15 text-sky-600 dark:text-sky-400">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </div>
        </div>
        <div class="mt-4">
            <p class="text-3xl font-extrabold tracking-tight text-sky-600 dark:text-sky-400">{{ $presentToday }}</p>
            <p class="mt-1 text-xs font-medium text-sky-600/80 dark:text-sky-400/80">Điểm danh đúng giờ</p>
        </div>
    </div>

    <!-- Late Today -->
    <div class="kpi-card flex flex-col justify-between">
        <div class="flex items-center justify-between">
            <span class="text-xs font-bold text-[var(--app-muted)]">Đi muộn hôm nay</span>
            <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-500/15 text-amber-600 dark:text-amber-400">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" x2="12" y1="9" y2="13"/><line x1="12" x2="12.01" y1="17" y2="17"/></svg>
            </div>
        </div>
        <div class="mt-4">
            <p class="text-3xl font-extrabold tracking-tight text-amber-600 dark:text-amber-400">{{ $lateToday }}</p>
            <p class="mt-1 text-xs font-medium text-amber-600/80 dark:text-amber-400/80">Vào ca sau quy định</p>
        </div>
    </div>

    <!-- Absent Today -->
    <div class="kpi-card flex flex-col justify-between">
        <div class="flex items-center justify-between">
            <span class="text-xs font-bold text-[var(--app-muted)]">Vắng mặt hôm nay</span>
            <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-rose-500/15 text-rose-600 dark:text-rose-400">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" x2="9" y1="9" y2="15"/><line x1="9" x2="15" y1="9" y2="15"/></svg>
            </div>
        </div>
        <div class="mt-4">
            <p class="text-3xl font-extrabold tracking-tight text-rose-600 dark:text-rose-400">{{ $absentToday }}</p>
            <p class="mt-1 text-xs font-medium text-rose-600/80 dark:text-rose-400/80">Chưa có check-in</p>
        </div>
    </div>
</div>

<!-- Pending Leave Requests Alert Banner -->
<div class="app-panel mt-6 flex flex-wrap items-center justify-between gap-4 border-l-4 border-l-amber-500 bg-gradient-to-r from-amber-500/5 via-[var(--app-surface)] to-[var(--app-surface)] p-5">
    <div class="flex items-center gap-3.5">
        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-amber-500/15 text-amber-600 dark:text-amber-400">
            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 2v4"/><path d="M16 2v4"/><rect width="18" height="18" x="3" y="4" rx="2"/><path d="M3 10h18"/><path d="m9 16 2 2 4-4"/></svg>
        </div>
        <div>
            <h2 class="text-sm font-bold text-[var(--app-text)] sm:text-base">Đơn nghỉ chờ duyệt</h2>
            <p class="text-xs text-[var(--app-muted)]">{{ $pendingLeaveRequests->count() }} đơn gần nhất cần xem xét</p>
        </div>
    </div>
    <div class="flex flex-wrap items-center gap-2">
        <a href="{{ route('employee.leave-requests.index') }}" class="app-button-secondary text-xs py-2 px-3.5">
            <span>Đơn nghỉ của tôi</span>
        </a>
        <a href="{{ route('hr.leave-requests.index', ['status' => 'pending']) }}" class="app-button-primary text-xs py-2 px-4">
            <span>Mở danh sách đơn nghỉ</span>
            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" d="M5 12h14M12 5l7 7-7 7"/></svg>
        </a>
    </div>
</div>

<!-- Two Column Charts Section -->
<div class="dashboard-two-column mt-6 grid gap-6">
    <!-- Chart 1: Attendance Status Donut -->
    <div class="app-panel p-5 sm:p-6 flex flex-col justify-between">
        <div class="flex items-start justify-between">
            <div>
                <h2 class="app-heading">Tình trạng chấm công tháng {{ sprintf('%02d/%d', $selectedMonth, $selectedYear) }}</h2>
                <p class="app-subtitle">Phân bổ tỷ lệ chuyên cần của toàn công ty</p>
            </div>
            <span class="app-badge app-badge-info">
                {{ $periodStatus->sum() }} lượt
            </span>
        </div>
        <div class="relative mt-5 h-72">
            @if($periodStatus->sum())
                <canvas id="attendance-status-chart" aria-label="Biểu đồ tình trạng chấm công"></canvas>
            @else
                <x-empty-state title="Chưa có dữ liệu thống kê trong khoảng thời gian này." />
            @endif
        </div>
    </div>

    <!-- Chart 2: Department Distribution Bars -->
    <div class="app-panel p-5 sm:p-6 flex flex-col justify-between">
        <div class="flex items-start justify-between">
            <div>
                <h2 class="app-heading">Cơ cấu nhân sự theo phòng ban</h2>
                <p class="app-subtitle">Số lượng nhân viên được phân bổ tại các bộ phận</p>
            </div>
            <span class="rounded-lg bg-indigo-500/10 px-2.5 py-1 text-xs font-bold text-indigo-600 dark:text-indigo-400">
                {{ $employeesByDepartment->count() }} phòng ban
            </span>
        </div>
        <div class="relative mt-5 h-72">
            @if($employeesByDepartment->count())
                <canvas id="department-chart" aria-label="Biểu đồ nhân sự theo phòng ban"></canvas>
            @else
                <x-empty-state title="Chưa có dữ liệu thống kê trong khoảng thời gian này." />
            @endif
        </div>
    </div>
</div>

<!-- Two Column: Trend & Role Breakdown -->
<div class="dashboard-two-column mt-6 grid gap-6">
    <!-- Chart 3: Trend 6 Months -->
    <div class="app-panel min-w-0 p-5 sm:p-6">
        <div class="flex items-start justify-between">
            <div>
                <h2 class="app-heading">Xu hướng chuyên cần 6 tháng gần nhất</h2>
                <p class="app-subtitle">Biến động số lượng có mặt, đi muộn và vắng theo thời gian</p>
            </div>
            <div class="flex items-center gap-3 text-xs font-semibold">
                <span class="inline-flex items-center gap-1 text-emerald-600 dark:text-emerald-400"><span class="h-2 w-2 rounded-full bg-emerald-500"></span> Có mặt</span>
                <span class="inline-flex items-center gap-1 text-amber-600 dark:text-amber-400"><span class="h-2 w-2 rounded-full bg-amber-500"></span> Muộn</span>
                <span class="inline-flex items-center gap-1 text-rose-600 dark:text-rose-400"><span class="h-2 w-2 rounded-full bg-rose-500"></span> Vắng</span>
            </div>
        </div>
        <div class="relative mt-5 h-72">
            <canvas id="attendance-trend-chart" aria-label="Biểu đồ xu hướng chuyên cần"></canvas>
        </div>
    </div>

    <!-- Role Breakdown Card -->
    <div class="app-panel p-5 sm:p-6 flex flex-col justify-between">
        <div>
            <div class="flex items-center justify-between border-b border-[var(--app-border)] pb-4">
                <div>
                    <h2 class="app-heading">Tài khoản theo role</h2>
                    <p class="app-subtitle">Tổng số: {{ $totalUsers }} tài khoản hệ thống</p>
                </div>
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-purple-500/15 text-purple-600 dark:text-purple-400">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10"/></svg>
                </div>
            </div>

            <div class="mt-5 space-y-4">
                @php
                    $roleConfig = [
                        'admin' => ['label' => 'Quản trị viên', 'desc' => 'Toàn quyền cấu hình & tài khoản', 'badge' => 'bg-purple-500/10 text-purple-700 dark:text-purple-400 border-purple-500/20'],
                        'hr' => ['label' => 'Chuyên viên Nhân sự', 'desc' => 'Quản lý hồ sơ, chấm công & duyệt phép', 'badge' => 'bg-indigo-500/10 text-indigo-700 dark:text-indigo-400 border-indigo-500/20'],
                        'employee' => ['label' => 'Nhân viên công ty', 'desc' => 'Tự chấm công, gửi đơn & xem công', 'badge' => 'bg-sky-500/10 text-sky-700 dark:text-sky-400 border-sky-500/20'],
                    ];
                @endphp
                @foreach($roleConfig as $roleKey => $info)
                    <div class="flex items-center justify-between rounded-xl bg-slate-50 dark:bg-slate-800/40 p-3 border border-[var(--app-border)]">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-bold text-[var(--app-text)]">{{ $info['label'] }}</span>
                                <span class="rounded-md border px-1.5 py-0.5 text-[10px] font-semibold {{ $info['badge'] }}">{{ ucfirst($roleKey) }}</span>
                            </div>
                            <p class="text-[11px] text-[var(--app-muted)] mt-0.5">{{ $info['desc'] }}</p>
                        </div>
                        <span class="text-lg font-extrabold text-[var(--app-text)]">{{ $usersByRole[$roleKey] ?? 0 }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="mt-6 pt-4 border-t border-[var(--app-border)] flex items-center justify-between">
            <span class="text-xs text-[var(--app-muted)]">Module HR</span>
            <a href="{{ route('hr.reports.index') }}" class="inline-flex items-center gap-1 text-xs font-bold text-indigo-600 hover:text-indigo-700 dark:text-indigo-400">
                <span>Mở báo cáo HR →</span>
            </a>
        </div>
    </div>
</div>

<!-- Recent Attendance Activity Table -->
<div class="app-panel mt-6 overflow-hidden">
    <div class="flex items-center justify-between border-b border-[var(--app-border)] p-5">
        <div>
            <h2 class="app-heading">Bản ghi chấm công gần đây</h2>
            <p class="app-subtitle">Cập nhật theo thời gian thực từ các điểm danh hợp lệ</p>
        </div>
        <a href="{{ route('hr.attendances.index') }}" class="app-button-secondary text-xs py-2 px-3.5">
            <span>Xem tất cả bản ghi</span>
        </a>
    </div>
    <div class="overflow-x-auto">
        <table class="app-table">
            <thead>
                <tr>
                    <th>Nhân viên</th>
                    <th>Phòng ban</th>
                    <th>Ngày ghi nhận</th>
                    <th>Trạng thái</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentAttendance as $attendance)
                    <tr>
                        <td>
                            <div class="flex items-center gap-3">
                                <x-user-avatar :user="$attendance->employee?->user" class="h-8 w-8 rounded-xl shadow-xs" />
                                <div>
                                    <p class="font-bold text-[var(--app-text)]">{{ $attendance->employee?->user?->name ?? '—' }}</p>
                                    <p class="text-[11px] text-[var(--app-muted)]">{{ $attendance->employee?->employee_code ?? '—' }}</p>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="font-medium text-[var(--app-text)]">{{ $attendance->employee?->department?->name ?? 'Chưa phân phòng' }}</span>
                        </td>
                        <td>
                            <span class="font-mono text-xs text-[var(--app-muted)]">{{ $attendance->work_date?->format('d/m/Y') ?? '—' }}</span>
                        </td>
                        <td>
                            <x-status-badge :status="$attendance->status" />
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="py-12 text-center text-[var(--app-muted)]">
                            <x-empty-state title="Chưa có dữ liệu chấm công" description="Các bản ghi chấm công gần nhất sẽ xuất hiện tại đây." />
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    if (typeof window.renderDashboardCharts === 'function') {
        window.renderDashboardCharts({
            attendanceStatus: {
                labels: ['Có mặt', 'Đi muộn', 'Vắng mặt', 'Nghỉ phép'],
                values: [{{ $periodStatus['present'] ?? 0 }}, {{ $periodStatus['late'] ?? 0 }}, {{ $periodStatus['absent'] ?? 0 }}, {{ $periodStatus['leave'] ?? 0 }}]
            },
            departments: {
                labels: @json($employeesByDepartment->pluck('name')->values()),
                values: @json($employeesByDepartment->pluck('total')->map(fn ($value) => (int) $value)->values())
            },
            trend: @json($trend),
        });
    }
});
</script>
@endpush
@endsection
