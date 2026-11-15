@php
    $previousShift = $openAttendance && $openAttendance->work_date->toDateString() !== now($schedule['timezone'])->toDateString();
    $displayAttendance = $previousShift ? $openAttendance : $todayAttendance;
    $shiftState = $previousShift
        ? 'Còn ca chưa kết thúc'
        : ($todayAttendance?->check_out ? 'Đã hoàn tất'
            : ($todayAttendance?->check_in ? 'Đang trong ca'
                : ($todayAttendance ? 'Đã ghi nhận' : 'Chưa chấm công')));
@endphp

<section class="app-panel overflow-hidden" aria-labelledby="shift-title">
    <div class="grid lg:grid-cols-[minmax(0,1.2fr)_minmax(0,0.8fr)]">
        <div class="p-6 sm:p-8">
            <div class="flex items-center gap-3">
                <x-user-avatar :user="$employee->user" class="h-12 w-12 rounded-xl" />
                <div class="min-w-0">
                    <p class="truncate font-semibold text-[var(--app-text)]">{{ $employee->user->name }}</p>
                    <p class="mt-0.5 text-xs text-[var(--app-muted)]">{{ $employee->employee_code }} · {{ $employee->department?->name ?? 'Chưa phân phòng ban' }}</p>
                </div>
            </div>

            <div class="mt-9">
                <p class="text-xs font-semibold uppercase tracking-[0.14em] text-[var(--app-muted)]">Trạng thái hôm nay</p>
                <h2 id="shift-title" class="mt-2 text-2xl font-bold tracking-tight text-[var(--app-text)] sm:text-3xl">{{ $shiftState }}</h2>
                @if ($todayAttendance)
                    <div class="mt-3"><x-status-badge :status="$todayAttendance->status" /></div>
                @endif
                @if ($previousShift)
                    <p class="mt-3 text-sm text-[var(--app-muted)]">Ra ca ngày {{ $openAttendance->work_date->format('d/m/Y') }} trước khi vào ca mới.</p>
                @endif
            </div>

            <div class="mt-8">
                @if ($previousShift)
                    <button type="button" @click="open('check-out')" class="app-button-primary min-w-44 py-3">Chấm công ra</button>
                @elseif (! $todayAttendance)
                    <button type="button" @click="open('check-in')" class="app-button-primary min-w-44 py-3">Chấm công vào</button>
                @elseif ($todayAttendance->check_in && ! $todayAttendance->check_out)
                    <button type="button" @click="open('check-out')" class="app-button-primary min-w-44 py-3">Chấm công ra</button>
                @endif
            </div>
        </div>

        <div class="border-t border-[var(--app-border)] bg-indigo-500/[0.03] p-6 dark:bg-indigo-500/[0.06] sm:p-8 lg:border-l lg:border-t-0">
            <p class="text-xs font-semibold uppercase tracking-[0.14em] text-[var(--app-muted)]">{{ $previousShift ? 'Ca ngày '.$openAttendance->work_date->format('d/m/Y') : 'Giờ đã ghi nhận' }}</p>
            <dl class="mt-5 divide-y divide-[var(--app-border)]">
                <div class="flex items-baseline justify-between gap-4 py-4 first:pt-0">
                    <dt class="text-sm text-[var(--app-muted)]">Vào ca</dt>
                    <dd class="font-mono text-2xl font-semibold tabular-nums text-[var(--app-text)]">{{ $displayAttendance?->check_in ?: '—:—:—' }}</dd>
                </div>
                <div class="flex items-baseline justify-between gap-4 py-4">
                    <dt class="text-sm text-[var(--app-muted)]">Ra ca</dt>
                    <dd class="font-mono text-2xl font-semibold tabular-nums text-[var(--app-text)]">{{ $displayAttendance?->check_out ?: '—:—:—' }}</dd>
                </div>
            </dl>
        </div>
    </div>

    <div class="flex flex-wrap gap-x-7 gap-y-2 border-t border-[var(--app-border)] px-6 py-4 text-xs text-[var(--app-muted)] sm:px-8">
        <span>Ca bắt đầu {{ $schedule['work_start_time'] }} · Đi muộn sau {{ $schedule['grace_boundary_time'] }}</span>
        <span>HUIT, 140 Lê Trọng Tấn · Bán kính {{ config('attendance.site_radius_meters') }} m</span>
    </div>
</section>
