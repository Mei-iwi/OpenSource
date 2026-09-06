@props(['status' => 'default', 'label' => null])
@php
    $labels = [
        'active' => 'Đang làm việc',
        'inactive' => 'Đã nghỉ việc',
        'locked' => 'Đã khóa',
        'present' => 'Có mặt',
        'late' => 'Đi muộn',
        'absent' => 'Vắng mặt',
        'leave' => 'Nghỉ phép',
        'admin' => 'Quản trị viên',
        'hr' => 'Nhân sự',
        'employee' => 'Nhân viên',
        'pending' => 'Chờ duyệt',
        'approved' => 'Đã duyệt',
        'rejected' => 'Từ chối',
        'cancelled' => 'Đã hủy',
    ];

    $styles = [
        'active' => ['bg' => 'bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border-emerald-500/20', 'dot' => 'bg-emerald-500 animate-pulse'],
        'present' => ['bg' => 'bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border-emerald-500/20', 'dot' => 'bg-emerald-500 animate-pulse'],
        'late' => ['bg' => 'bg-amber-500/10 text-amber-700 dark:text-amber-400 border-amber-500/20', 'dot' => 'bg-amber-500'],
        'absent' => ['bg' => 'bg-rose-500/10 text-rose-700 dark:text-rose-400 border-rose-500/20', 'dot' => 'bg-rose-500'],
        'leave' => ['bg' => 'bg-sky-500/10 text-sky-700 dark:text-sky-400 border-sky-500/20', 'dot' => 'bg-sky-500'],
        'locked' => ['bg' => 'bg-rose-500/10 text-rose-700 dark:text-rose-400 border-rose-500/20', 'dot' => 'bg-rose-500'],
        'inactive' => ['bg' => 'bg-slate-500/10 text-slate-600 dark:text-slate-400 border-slate-500/20', 'dot' => 'bg-slate-400'],
        'admin' => ['bg' => 'bg-violet-500/10 text-violet-700 dark:text-violet-400 border-violet-500/20', 'dot' => 'bg-violet-500'],
        'hr' => ['bg' => 'bg-indigo-500/10 text-indigo-700 dark:text-indigo-400 border-indigo-500/20', 'dot' => 'bg-indigo-500'],
        'employee' => ['bg' => 'bg-blue-500/10 text-blue-700 dark:text-blue-400 border-blue-500/20', 'dot' => 'bg-blue-500'],
        'pending' => ['bg' => 'bg-amber-500/10 text-amber-700 dark:text-amber-400 border-amber-500/20', 'dot' => 'bg-amber-500 animate-pulse'],
        'approved' => ['bg' => 'bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border-emerald-500/20', 'dot' => 'bg-emerald-500'],
        'rejected' => ['bg' => 'bg-rose-500/10 text-rose-700 dark:text-rose-400 border-rose-500/20', 'dot' => 'bg-rose-500'],
        'cancelled' => ['bg' => 'bg-slate-500/10 text-slate-600 dark:text-slate-400 border-slate-500/20', 'dot' => 'bg-slate-400'],
    ];

    $current = $styles[$status] ?? ['bg' => 'bg-slate-500/10 text-slate-600 dark:text-slate-400 border-slate-500/20', 'dot' => 'bg-slate-400'];
    $displayLabel = $label ?? ($labels[$status] ?? ucfirst($status));
@endphp

<span class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-xs font-semibold {{ $current['bg'] }}">
    <span class="h-1.5 w-1.5 rounded-full {{ $current['dot'] }}"></span>
    <span>{{ $displayLabel }}</span>
</span>
