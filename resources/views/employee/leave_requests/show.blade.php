@extends('layouts.app')
@section('title', 'Chi tiết đơn nghỉ')
@section('content')
<x-page-header eyebrow="Cá nhân" title="Chi tiết đơn nghỉ" description="Thông tin và trạng thái xử lý đơn xin nghỉ.">
    <a href="{{ route('employee.leave-requests.index') }}" class="app-button-secondary">Quay lại</a>
</x-page-header>

<div class="app-panel max-w-3xl p-5 sm:p-6">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3 border-b border-[var(--app-border)] pb-5">
        <div>
            <p class="text-lg font-bold text-[var(--app-text)]">{{ ['annual'=>'Nghỉ phép năm','sick'=>'Nghỉ ốm','unpaid'=>'Nghỉ không lương','other'=>'Khác'][$leaveRequest->leave_type] ?? $leaveRequest->leave_type }}</p>
            <p class="mt-1 text-sm text-[var(--app-muted)]">Gửi ngày: {{ $leaveRequest->created_at->format('d/m/Y H:i') }}</p>
        </div>
        <div>
            <x-status-badge :status="$leaveRequest->status" :label="['pending'=>'Đang chờ','approved'=>'Đã duyệt','rejected'=>'Từ chối','cancelled'=>'Đã hủy'][$leaveRequest->status] ?? $leaveRequest->status" />
        </div>
    </div>

    <dl class="grid gap-5 sm:grid-cols-2">
        <div>
            <dt class="app-label">Thời gian xin nghỉ</dt>
            <dd class="mt-1 font-medium text-[var(--app-text)]">{{ $leaveRequest->start_date->format('d/m/Y') }} - {{ $leaveRequest->end_date->format('d/m/Y') }} ({{ $leaveRequest->start_date->diffInDays($leaveRequest->end_date) + 1 }} ngày)</dd>
        </div>
        <div>
            <dt class="app-label">Trạng thái</dt>
            <dd class="mt-1 text-sm font-semibold text-[var(--app-text)]">{{ ['pending'=>'Đang chờ duyệt','approved'=>'Đã duyệt thành công','rejected'=>'Đã bị từ chối','cancelled'=>'Đã hủy bỏ'][$leaveRequest->status] ?? $leaveRequest->status }}</dd>
        </div>
        <div class="sm:col-span-2">
            <dt class="app-label">Lý do xin nghỉ</dt>
            <dd class="mt-1 whitespace-pre-line rounded-xl border border-[var(--app-border)] bg-slate-50/60 p-3.5 text-sm text-[var(--app-text)] dark:bg-slate-900/40">{{ $leaveRequest->reason }}</dd>
        </div>
    </dl>

    {{-- Review details section: strictly differentiate status --}}
    @if($leaveRequest->status === 'approved' && $leaveRequest->reviewed_at)
        <div class="mt-6 rounded-2xl border border-emerald-500/30 bg-emerald-500/5 p-5 dark:bg-emerald-950/20">
            <div class="flex items-center gap-2 text-emerald-800 dark:text-emerald-300">
                <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <h3 class="text-base font-bold">Thông tin phê duyệt</h3>
            </div>
            <dl class="mt-4 grid gap-4 sm:grid-cols-2 text-sm">
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wider text-emerald-700/80 dark:text-emerald-400">Người duyệt</dt>
                    <dd class="mt-1 font-semibold text-[var(--app-text)]">{{ $leaveRequest->reviewer_display ?? $leaveRequest->reviewer?->name ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wider text-emerald-700/80 dark:text-emerald-400">Thời gian duyệt</dt>
                    <dd class="mt-1 text-[var(--app-muted)]">{{ $leaveRequest->reviewed_at->format('d/m/Y H:i') }}</dd>
                </div>
                @if($leaveRequest->review_note)
                    <div class="sm:col-span-2">
                        <dt class="text-xs font-semibold uppercase tracking-wider text-emerald-700/80 dark:text-emerald-400">Phản hồi từ quản trị</dt>
                        <dd class="mt-1.5 whitespace-pre-line rounded-xl border border-emerald-500/20 bg-white/80 p-3 text-sm text-[var(--app-text)] dark:bg-slate-900/60">{{ $leaveRequest->review_note }}</dd>
                    </div>
                @endif
            </dl>
        </div>
    @elseif($leaveRequest->status === 'rejected' && $leaveRequest->reviewed_at)
        <div class="mt-6 rounded-2xl border border-rose-500/30 bg-rose-500/5 p-5 dark:bg-rose-950/20">
            <div class="flex items-center gap-2 text-rose-800 dark:text-rose-300">
                <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 8.25h.01" />
                </svg>
                <h3 class="text-base font-bold">Thông tin từ chối đơn</h3>
            </div>
            <dl class="mt-4 grid gap-4 sm:grid-cols-2 text-sm">
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wider text-rose-700/80 dark:text-rose-400">Người từ chối</dt>
                    <dd class="mt-1 font-semibold text-[var(--app-text)]">{{ $leaveRequest->reviewer_display ?? $leaveRequest->reviewer?->name ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wider text-rose-700/80 dark:text-rose-400">Thời gian từ chối</dt>
                    <dd class="mt-1 text-[var(--app-muted)]">{{ $leaveRequest->reviewed_at->format('d/m/Y H:i') }}</dd>
                </div>
                <div class="sm:col-span-2">
                    <dt class="text-xs font-semibold uppercase tracking-wider text-rose-700/80 dark:text-rose-400">Lý do từ chối</dt>
                    @if($leaveRequest->review_note)
                        <dd class="mt-1.5 whitespace-pre-line rounded-xl border border-rose-500/20 bg-white/80 p-3 text-sm text-[var(--app-text)] dark:bg-slate-900/60">{{ $leaveRequest->review_note }}</dd>
                    @else
                        <dd class="mt-1.5 italic text-sm text-[var(--app-muted)]">(Không có lý do kèm theo)</dd>
                    @endif
                </div>
            </dl>
        </div>
    @elseif($leaveRequest->status === 'cancelled')
        <div class="mt-6 rounded-2xl border border-slate-300 bg-slate-50/70 p-4 text-sm text-slate-600 dark:border-slate-700 dark:bg-slate-800/40 dark:text-slate-400">
            <span class="font-semibold">Trạng thái:</span> Bạn đã chủ động hủy bỏ đơn xin nghỉ này.
        </div>
    @elseif($leaveRequest->status === 'pending')
        <div class="mt-6 rounded-2xl border border-amber-500/30 bg-amber-500/5 p-4 text-sm text-amber-800 dark:text-amber-300">
            Đơn của bạn đang trong danh sách chờ xét duyệt bởi phòng Nhân sự hoặc Quản trị viên.
        </div>
    @endif

    @if($leaveRequest->status === 'pending')
        <form method="POST" action="{{ route('employee.leave-requests.cancel', $leaveRequest) }}" class="mt-6 border-t border-[var(--app-border)] pt-5">
            @csrf
            @method('PATCH')
            <button type="submit" class="rounded-xl border border-red-200 px-4 py-2.5 text-sm font-semibold text-red-600 hover:bg-red-50" onclick="return confirm('Hủy đơn xin nghỉ này?')">Hủy đơn</button>
        </form>
    @endif
</div>
@endsection
