@extends('layouts.app')
@section('title', 'Tổng quan Nhân viên')
@section('content')
<x-page-header eyebrow="Khu vực Cá nhân" title="Tổng quan của tôi" description="Theo dõi chi tiết thời gian làm việc, chỉ số chuyên cần và hồ sơ cá nhân.">
    <div class="flex flex-wrap items-center gap-2.5">
        <a href="{{ route('me.attendance.index') }}" class="app-button-primary">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14.5 4h-5L7 7H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-3l-2.5-3z"/><circle cx="12" cy="13" r="3"/></svg>
            <span>Tự chấm công ngay</span>
        </a>
        <a href="{{ route('employee.leave-requests.create') }}" class="app-button-secondary">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
            <span>Gửi đơn xin nghỉ</span>
        </a>
        <a href="{{ route('employee.profile.edit') }}" class="app-button-secondary">
            <span>Hồ sơ</span>
        </a>
    </div>
</x-page-header>

@if($employee)
    <!-- Employee Identity Card -->
    <div class="app-panel mb-6 flex flex-col gap-5 p-5 sm:flex-row sm:items-center sm:p-6 bg-gradient-to-r from-slate-50 via-[var(--app-surface)] to-indigo-50/20 dark:from-slate-800/40 dark:via-[var(--app-surface)] dark:to-indigo-950/20">
        <div class="relative flex h-20 w-20 shrink-0 items-center justify-center overflow-hidden rounded-2xl bg-gradient-to-tr from-indigo-600 via-indigo-500 to-sky-400 text-2xl font-bold text-white shadow-md shadow-indigo-500/20 ring-4 ring-indigo-500/15">
            @if($employee->avatar_path)
                <img src="{{ $employee->user->avatar_url }}" alt="Ảnh đại diện {{ $employee->user->name }}" class="h-full w-full object-cover">
            @else
                {{ strtoupper(substr($employee->user->name, 0, 1)) }}
            @endif
        </div>
        <div class="min-w-0 flex-1">
            <div class="flex flex-wrap items-center gap-2">
                <h2 class="truncate text-xl font-extrabold text-[var(--app-text)] sm:text-2xl">{{ $employee->user->name }}</h2>
                <span class="inline-flex items-center gap-1 rounded-full bg-emerald-500/10 px-2.5 py-0.5 text-xs font-semibold text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    Đang làm việc
                </span>
            </div>
            <div class="mt-2.5 flex flex-wrap gap-x-6 gap-y-1.5 text-xs text-[var(--app-muted)]">
                <div class="flex items-center gap-1.5">
                    <span class="font-medium">Mã NV:</span>
                    <span class="font-bold font-mono text-[var(--app-text)]">{{ $employee->employee_code }}</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="font-medium">Phòng ban:</span>
                    <span class="font-bold text-[var(--app-text)]">{{ $employee->department?->name ?? 'Chưa phân bổ' }}</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="font-medium">Chức vụ:</span>
                    <span class="font-bold text-[var(--app-text)]">{{ $employee->position ?: 'Chuyên viên' }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Bento Grid: Employee Monthly Attendance KPIs -->
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
        <!-- Present -->
        <div class="kpi-card flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-[var(--app-muted)]">Có mặt</span>
                <div class="flex h-8 w-8 items-center justify-center rounded-xl bg-emerald-500/15 text-emerald-600 dark:text-emerald-400">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                </div>
            </div>
            <div class="mt-4">
                <p class="text-3xl font-extrabold text-emerald-600 dark:text-emerald-400">{{ (int) ($summary?->present ?? 0) }}</p>
                <p class="mt-1 text-xs text-[var(--app-muted)]">Tháng {{ $currentMonth }}</p>
            </div>
        </div>

        <!-- Late -->
        <div class="kpi-card flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-[var(--app-muted)]">Đi muộn</span>
                <div class="flex h-8 w-8 items-center justify-center rounded-xl bg-amber-500/15 text-amber-600 dark:text-amber-400">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                </div>
            </div>
            <div class="mt-4">
                <p class="text-3xl font-extrabold text-amber-600 dark:text-amber-400">{{ (int) ($summary?->late ?? 0) }}</p>
                <p class="mt-1 text-xs text-[var(--app-muted)]">Tháng {{ $currentMonth }}</p>
            </div>
        </div>

        <!-- Absent -->
        <div class="kpi-card flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-[var(--app-muted)]">Vắng mặt</span>
                <div class="flex h-8 w-8 items-center justify-center rounded-xl bg-rose-500/15 text-rose-600 dark:text-rose-400">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" x2="9" y1="9" y2="15"/><line x1="9" x2="15" y1="9" y2="15"/></svg>
                </div>
            </div>
            <div class="mt-4">
                <p class="text-3xl font-extrabold text-rose-600 dark:text-rose-400">{{ (int) ($summary?->absent ?? 0) }}</p>
                <p class="mt-1 text-xs text-[var(--app-muted)]">Tháng {{ $currentMonth }}</p>
            </div>
        </div>

        <!-- Leave -->
        <div class="kpi-card flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-[var(--app-muted)]">Nghỉ phép</span>
                <div class="flex h-8 w-8 items-center justify-center rounded-xl bg-purple-500/15 text-purple-600 dark:text-purple-400">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 2v4"/><path d="M16 2v4"/><rect width="18" height="18" x="3" y="4" rx="2"/><path d="M3 10h18"/></svg>
                </div>
            </div>
            <div class="mt-4">
                <p class="text-3xl font-extrabold text-purple-600 dark:text-purple-400">{{ (int) ($summary?->leave_total ?? 0) }}</p>
                <p class="mt-1 text-xs text-[var(--app-muted)]">Được duyệt</p>
            </div>
        </div>

        <!-- Worked Hours -->
        <div class="kpi-card flex flex-col justify-between sm:col-span-2 lg:col-span-1">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-[var(--app-muted)]">Tổng giờ công</span>
                <div class="flex h-8 w-8 items-center justify-center rounded-xl bg-sky-500/15 text-sky-600 dark:text-sky-400">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                </div>
            </div>
            <div class="mt-4">
                <p class="text-3xl font-extrabold text-sky-600 dark:text-sky-400">{{ $workedHours }} <span class="text-sm font-semibold text-[var(--app-muted)]">giờ</span></p>
                <p class="mt-1 text-xs text-[var(--app-muted)]">Tích lũy tháng</p>
            </div>
        </div>
    </div>

    <!-- Two Columns: Recent Attendance & Attendance Progress Indicators -->
    <div class="dashboard-two-column mt-6 grid items-stretch gap-6">
        <!-- Recent Attendance Table -->
        <div class="app-panel min-w-0 p-5 sm:p-6 flex flex-col justify-between">
            <div>
                <div class="flex items-start justify-between gap-3 border-b border-[var(--app-border)] pb-4">
                    <div>
                        <h2 class="app-heading">Lịch sử chấm công gần đây</h2>
                        <p class="app-subtitle">Bản ghi trong tháng {{ $currentMonth }} của bạn</p>
                    </div>
                    <a href="{{ route('employee.attendances.index') }}" class="app-button-secondary text-xs py-1.5 px-3">Xem tất cả</a>
                </div>

                <div class="mt-4 overflow-x-auto">
                    <table class="app-table">
                        <thead>
                            <tr>
                                <th>Ngày</th>
                                <th>Giờ vào</th>
                                <th>Giờ ra</th>
                                <th>Trạng thái</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentAttendance as $attendance)
                                <tr>
                                    <td class="font-mono text-xs font-medium">{{ $attendance->work_date?->format('d/m/Y') }}</td>
                                    <td class="font-mono text-xs">{{ $attendance->check_in ?: '—' }}</td>
                                    <td class="font-mono text-xs">{{ $attendance->check_out ?: '—' }}</td>
                                    <td><x-status-badge :status="$attendance->status" /></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-12 text-center text-[var(--app-muted)]">
                                        <x-empty-state title="Chưa có dữ liệu chấm công" description="Hãy thực hiện tự chấm công để ghi nhận giờ làm." />
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Attendance Scores & Performance -->
        <div class="app-panel p-5 sm:p-6 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between border-b border-[var(--app-border)] pb-4">
                    <div>
                        <h2 class="app-heading">Chỉ số chuyên cần cá nhân</h2>
                        <p class="app-subtitle">Đánh giá khách quan dựa trên thời gian thực</p>
                    </div>
                    <span class="rounded-lg bg-emerald-500/10 px-2 py-1 text-xs font-bold text-emerald-600 dark:text-emerald-400">KPI Chuyên cần</span>
                </div>

                <div class="mt-6 space-y-6">
                    <!-- Score 1: Attendance Rate -->
                    <div>
                        <div class="flex justify-between text-sm mb-2">
                            <span class="font-semibold text-[var(--app-text)]">Tỷ lệ có mặt làm việc</span>
                            <strong class="font-bold text-emerald-600 dark:text-emerald-400">{{ $attendanceRate }}%</strong>
                        </div>
                        <div class="h-2.5 w-full rounded-full bg-slate-100 dark:bg-slate-800 overflow-hidden">
                            <div class="h-full rounded-full bg-gradient-to-r from-emerald-500 to-teal-400 transition-all duration-500" style="width: {{ min(100, $attendanceRate) }}%"></div>
                        </div>
                        <p class="mt-1.5 text-[11px] text-[var(--app-muted)]">Dựa trên tổng ngày làm việc tiêu chuẩn</p>
                    </div>

                    <!-- Score 2: Punctuality Rate -->
                    <div>
                        <div class="flex justify-between text-sm mb-2">
                            <span class="font-semibold text-[var(--app-text)]">Tỷ lệ đi làm đúng giờ</span>
                            <strong class="font-bold text-sky-600 dark:text-sky-400">{{ $punctualityRate }}%</strong>
                        </div>
                        <div class="h-2.5 w-full rounded-full bg-slate-100 dark:bg-slate-800 overflow-hidden">
                            <div class="h-full rounded-full bg-gradient-to-r from-indigo-500 to-sky-400 transition-all duration-500" style="width: {{ min(100, $punctualityRate) }}%"></div>
                        </div>
                        <p class="mt-1.5 text-[11px] text-[var(--app-muted)]">Tỷ lệ không vi phạm đi muộn</p>
                    </div>
                </div>
            </div>

            <div class="mt-6 pt-4 border-t border-[var(--app-border)] flex items-center justify-between">
                <span class="text-xs text-[var(--app-muted)]">Cần nghỉ phép?</span>
                <a href="{{ route('employee.leave-requests.create') }}" class="text-xs font-bold text-indigo-600 hover:text-indigo-700 dark:text-indigo-400">Tạo đơn nghỉ phép ngay</a>
            </div>
        </div>
    </div>
@else
    <div class="app-panel p-8 text-center">
        <x-empty-state title="Chưa có hồ sơ nhân viên" description="Tài khoản chưa được liên kết với hồ sơ nhân sự. Vui lòng liên hệ Quản trị viên." />
    </div>
@endif
@endsection
