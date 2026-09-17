@extends('layouts.app')
@section('title', 'Tổng quan Nhân sự')
@section('content')
<x-page-header eyebrow="Khu vực Quản trị Nhân sự" title="Tổng quan vận hành HR" description="Giám sát biến động nhân sự, tỷ lệ chuyên cần và danh mục phòng ban toàn diện.">
    <a href="{{ route('hr.reports.index') }}" class="app-button-primary">
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" x2="18" y1="20" y2="10"/><line x1="12" x2="12" y1="20" y2="4"/><line x1="6" x2="6" y1="20" y2="14"/></svg>
        <span>Báo cáo chuyên sâu</span>
    </a>
</x-page-header>

<!-- Filter Toolbar -->
<form method="GET" class="app-panel mb-6 flex flex-wrap items-center justify-between gap-4 p-4" aria-label="Bộ lọc dashboard">
    <div class="flex flex-wrap items-center gap-3">
        <div class="flex items-center gap-2">
            <span class="text-xs font-bold uppercase tracking-wider text-[var(--app-muted)]">Kỳ tháng:</span>
            <select id="hr-month" name="month" class="app-input text-xs font-semibold py-2">
                <option value="">Tháng hiện tại</option>
                @foreach(range(1, 12) as $month)
                    <option value="{{ $month }}" @selected($selectedMonth === $month)>Tháng {{ $month }}</option>
                @endforeach
            </select>
            <select id="hr-year" name="year" class="app-input text-xs font-semibold py-2">
                @foreach(range(now()->year - 2, now()->year + 1) as $year)
                    <option value="{{ $year }}" @selected($selectedYear === $year)>Năm {{ $year }}</option>
                @endforeach
            </select>
        </div>
        <button class="app-button-primary text-xs py-2 px-3.5" type="submit">
            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
            <span>Lọc dữ liệu</span>
        </button>
        <a href="{{ route('hr.dashboard') }}" class="app-button-secondary text-xs py-2 px-3.5">Đặt lại</a>
    </div>

    <div class="flex items-center gap-2">
        <span class="text-xs text-[var(--app-muted)]">Tháng đang xem:</span>
        <span class="rounded-lg bg-indigo-500/10 px-2.5 py-1 text-xs font-bold text-indigo-600 dark:text-indigo-400 border border-indigo-500/20">
            {{ sprintf('%02d/%d', $selectedMonth, $selectedYear) }}
        </span>
    </div>
</form>

<!-- Bento Grid KPI Cards -->
<div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
    <!-- Total Personnel -->
    <div class="kpi-card flex flex-col justify-between">
        <div class="flex items-center justify-between">
            <span class="text-xs font-bold text-[var(--app-muted)]">Tổng nhân sự</span>
            <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-500/15 text-indigo-600 dark:text-indigo-400">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </div>
        </div>
        <div class="mt-4">
            <p class="text-3xl font-extrabold tracking-tight text-[var(--app-text)]">{{ $totalEmployees }}</p>
            <p class="mt-1 text-xs font-semibold text-emerald-600 dark:text-emerald-400">Hồ sơ lưu trữ hệ thống</p>
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
            <p class="mt-1 text-xs font-medium text-[var(--app-muted)]">Trạng thái Active</p>
        </div>
    </div>

    <!-- Inactive Employees -->
    <div class="kpi-card flex flex-col justify-between">
        <div class="flex items-center justify-between">
            <span class="text-xs font-bold text-[var(--app-muted)]">Ngừng làm việc</span>
            <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-slate-500/15 text-slate-600 dark:text-slate-400">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="4.93" x2="19.07" y1="4.93" y2="19.07"/></svg>
            </div>
        </div>
        <div class="mt-4">
            <p class="text-3xl font-extrabold tracking-tight text-slate-500 dark:text-slate-400">{{ $inactiveEmployees }}</p>
            <p class="mt-1 text-xs font-medium text-[var(--app-muted)]">Trạng thái Inactive</p>
        </div>
    </div>

    <!-- Departments -->
    <div class="kpi-card flex flex-col justify-between">
        <div class="flex items-center justify-between">
            <span class="text-xs font-bold text-[var(--app-muted)]">Khối phòng ban</span>
            <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-sky-500/15 text-sky-600 dark:text-sky-400">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="16" height="20" x="4" y="2" rx="2" ry="2"/><path d="M9 22v-4h6v4"/></svg>
            </div>
        </div>
        <div class="mt-4">
            <p class="text-3xl font-extrabold tracking-tight text-sky-600 dark:text-sky-400">{{ $totalDepartments }}</p>
            <p class="mt-1 text-xs font-medium text-sky-600/80 dark:text-sky-400/80">Bộ máy vận hành</p>
        </div>
    </div>
</div>

<!-- Two Column Charts Section -->
<div class="dashboard-two-column mt-6 grid gap-6">
    <!-- Chart: Department Breakdown -->
    <div class="app-panel p-5 sm:p-6 flex flex-col justify-between">
        <div class="flex items-start justify-between">
            <div>
                <h2 class="app-heading">Nhân sự theo phòng ban</h2>
                <p class="app-subtitle">Phân bổ nhân lực tại từng bộ phận chức năng</p>
            </div>
            <span class="rounded-lg bg-indigo-500/10 px-2.5 py-1 text-xs font-bold text-indigo-600 dark:text-indigo-400">
                {{ $employeesByDepartment->count() }} phòng ban
            </span>
        </div>
        <div class="relative mt-5 h-72">
            @if($employeesByDepartment->count())
                <canvas id="department-chart" aria-label="Biểu đồ nhân sự theo phòng ban"></canvas>
            @else
                <x-empty-state title="Chưa có dữ liệu thống kê phòng ban." />
            @endif
        </div>
    </div>

    <!-- Chart: Monthly Attendance Donut -->
    <div class="app-panel p-5 sm:p-6 flex flex-col justify-between">
        <div class="flex items-start justify-between">
            <div>
                <h2 class="app-heading">Tình trạng chấm công tháng {{ $currentMonth }}</h2>
                <p class="app-subtitle">Tổng hợp tỷ lệ chuyên cần các ngày trong kỳ</p>
            </div>
            <span class="app-badge app-badge-info">
                {{ $attendanceByStatus->sum() }} bản ghi
            </span>
        </div>
        <div class="relative mt-5 h-72">
            @if($attendanceByStatus->sum())
                <canvas id="attendance-status-chart" aria-label="Biểu đồ tình trạng chấm công"></canvas>
            @else
                <x-empty-state title="Chưa có dữ liệu thống kê trong khoảng thời gian này." />
            @endif
        </div>
    </div>
</div>

<!-- Two Column: Trend & Quick Links -->
<div class="dashboard-two-column mt-6 grid gap-6">
    <!-- Trend Chart -->
    <div class="app-panel min-w-0 p-5 sm:p-6">
        <div class="flex items-start justify-between">
            <div>
                <h2 class="app-heading">Xu hướng chuyên cần 6 tháng gần nhất</h2>
                <p class="app-subtitle">Biến động số bản ghi có mặt, đi muộn và vắng</p>
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

    <!-- Quick Operations Panel -->
    <div class="app-panel p-5 sm:p-6 flex flex-col justify-between">
        <div>
            <div class="flex items-center justify-between border-b border-[var(--app-border)] pb-4">
                <div>
                    <h2 class="app-heading">Lối tắt tác vụ (Quick Links)</h2>
                    <p class="app-subtitle">Truy cập nhanh các nghiệp vụ nhân sự thường dùng</p>
                </div>
                <span class="rounded-lg bg-indigo-500/10 px-2 py-1 text-xs font-bold text-indigo-600 dark:text-indigo-400">HR Tools</span>
            </div>

            <div class="mt-4 space-y-2">
                <a href="{{ route('hr.employees.index') }}" class="app-quick-link">
                    <span class="flex items-center gap-2.5">
                        <svg class="h-4 w-4 text-indigo-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        Quản lý hồ sơ nhân viên
                    </span>
                </a>
                <a href="{{ route('hr.departments.index') }}" class="app-quick-link">
                    <span class="flex items-center gap-2.5">
                        <svg class="h-4 w-4 text-sky-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="16" height="20" x="4" y="2" rx="2"/><path d="M9 22v-4h6v4"/></svg>
                        Quản lý sơ đồ phòng ban
                    </span>
                </a>
                <a href="{{ route('hr.attendances.index') }}" class="app-quick-link">
                    <span class="flex items-center gap-2.5">
                        <svg class="h-4 w-4 text-emerald-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        Bảng chấm công tổng thể
                    </span>
                </a>
                <a href="{{ route('hr.reports.index') }}" class="app-quick-link">
                    <span class="flex items-center gap-2.5">
                        <svg class="h-4 w-4 text-purple-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" x2="18" y1="20" y2="10"/><line x1="12" x2="12" y1="20" y2="4"/><line x1="6" x2="6" y1="20" y2="14"/></svg>
                        Xuất báo cáo & Dữ liệu CSV
                    </span>
                </a>
                <a href="{{ route('hr.leave-requests.index', ['status' => 'pending']) }}" class="app-quick-link">
                    <span class="flex items-center gap-2.5">
                        <svg class="h-4 w-4 text-amber-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 2v4"/><path d="M16 2v4"/><rect width="18" height="18" x="3" y="4" rx="2"/><path d="M3 10h18"/><path d="m9 16 2 2 4-4"/></svg>
                        Đơn xin nghỉ chờ phê duyệt
                    </span>
                    <span class="inline-flex items-center rounded-full bg-amber-500/15 border border-amber-500/30 px-2 py-0.5 text-xs font-bold text-amber-600 dark:text-amber-400">
                        {{ $pendingLeaveCount }} đơn chờ duyệt
                    </span>
                </a>
            </div>
        </div>

        <div class="mt-4 pt-3 border-t border-[var(--app-border)]">
            <p class="text-xs text-[var(--app-muted)]">{{ $pendingLeaveCount }} đơn chờ duyệt cần xử lý trong hệ thống.</p>
        </div>
    </div>
</div>

<!-- Recent Attendance Table -->
<div class="app-panel mt-6 overflow-hidden">
    <div class="flex items-center justify-between border-b border-[var(--app-border)] p-5">
        <div>
            <h2 class="app-heading">Chấm công nhân sự gần đây</h2>
            <p class="app-subtitle">Theo dõi hoạt động vào/ra ca mới nhất của nhân viên</p>
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
                    <th>Ngày làm việc</th>
                    <th>Trạng thái</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentAttendance as $attendance)
                    <tr>
                        <td>
                            <div class="flex items-center gap-3">
                                <div class="flex h-8 w-8 items-center justify-center rounded-xl bg-gradient-to-tr from-indigo-600 to-sky-500 text-xs font-bold text-white shadow-xs">
                                    {{ strtoupper(substr($attendance->employee?->user?->name ?? 'N', 0, 1)) }}
                                </div>
                                <div>
                                    <p class="font-bold text-[var(--app-text)]">{{ $attendance->employee?->user?->name ?? '—' }}</p>
                                    <p class="text-[11px] text-[var(--app-muted)]">{{ $attendance->employee?->employee_code ?? '—' }}</p>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="font-medium text-[var(--app-text)]">{{ $attendance->employee?->department?->name ?? '—' }}</span>
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
                values: [{{ $attendanceByStatus['present'] ?? 0 }}, {{ $attendanceByStatus['late'] ?? 0 }}, {{ $attendanceByStatus['absent'] ?? 0 }}, {{ $attendanceByStatus['leave'] ?? 0 }}]
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
