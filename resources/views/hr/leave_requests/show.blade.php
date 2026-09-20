@extends('layouts.app')
@section('title', 'Chi tiết đơn nghỉ')
@section('content')
<x-page-header eyebrow="HR / Đơn nghỉ" title="Chi tiết đơn nghỉ" description="Xem thông tin và xử lý yêu cầu của nhân viên."><a href="{{ route('hr.leave-requests.index') }}" class="app-button-secondary">Quay lại</a></x-page-header>
<div class="app-panel max-w-3xl p-5 sm:p-6">
    <div class="mb-6 border-b border-[var(--app-border)] pb-5">
        <p class="text-lg font-bold text-[var(--app-text)]">{{ $leaveRequest->employee?->user?->name ?? '—' }}</p>
        <p class="mt-1 text-sm text-[var(--app-muted)]">{{ $leaveRequest->employee?->employee_code }} · {{ $leaveRequest->employee?->department?->name }}</p>
    </div>
    <dl class="grid gap-5 sm:grid-cols-2">
        <div>
            <dt class="app-label">Loại nghỉ</dt>
            <dd class="mt-1">{{ ['annual'=>'Nghỉ phép năm','sick'=>'Nghỉ ốm','unpaid'=>'Nghỉ không lương','other'=>'Khác'][$leaveRequest->leave_type] ?? $leaveRequest->leave_type }}</dd>
        </div>
        <div>
            <dt class="app-label">Trạng thái</dt>
            <dd class="mt-1"><x-status-badge :status="$leaveRequest->status" :label="['pending'=>'Đang chờ','approved'=>'Đã duyệt','rejected'=>'Từ chối','cancelled'=>'Đã hủy'][$leaveRequest->status] ?? $leaveRequest->status" /></dd>
        </div>
        <div>
            <dt class="app-label">Thời gian</dt>
            <dd class="mt-1">{{ $leaveRequest->start_date->format('d/m/Y') }} - {{ $leaveRequest->end_date->format('d/m/Y') }}</dd>
        </div>
        <div>
            <dt class="app-label">Ngày gửi</dt>
            <dd class="mt-1">{{ $leaveRequest->created_at->format('d/m/Y H:i') }}</dd>
        </div>
        <div class="sm:col-span-2">
            <dt class="app-label">Lý do</dt>
            <dd class="mt-1 whitespace-pre-line">{{ $leaveRequest->reason }}</dd>
        </div>
        @if($leaveRequest->reviewed_at)
            <div>
                <dt class="app-label">Người xử lý</dt>
                <dd class="mt-1 text-sm font-semibold text-[var(--app-text)]">{{ $leaveRequest->reviewer?->name ?? '—' }}</dd>
            </div>
            <div>
                <dt class="app-label">Thời gian xử lý</dt>
                <dd class="mt-1 text-sm text-[var(--app-muted)]">{{ $leaveRequest->reviewed_at->format('d/m/Y H:i') }}</dd>
            </div>
            @if($leaveRequest->review_note)
                <div class="sm:col-span-2">
                    <dt class="app-label">Ghi chú xử lý</dt>
                    <dd class="mt-1 whitespace-pre-line rounded-xl border border-[var(--app-border)] bg-slate-50/60 p-3 text-sm text-[var(--app-text)] dark:bg-slate-800/40">{{ $leaveRequest->review_note }}</dd>
                </div>
            @endif
        @endif
    </dl>
    @if($leaveRequest->status === 'pending')
        <form method="POST" action="{{ route('hr.leave-requests.review', $leaveRequest) }}" class="mt-6 border-t border-[var(--app-border)] pt-5">
            @csrf
            @method('PATCH')
            <label for="review_note" class="app-label">Ghi chú xử lý</label>
            <textarea id="review_note" name="review_note" rows="3" class="app-input mt-2 w-full" placeholder="Nhập lý do phê duyệt hoặc từ chối..."></textarea>
            <div class="mt-4 flex flex-wrap gap-3">
                <button name="status" value="approved" class="app-button-primary" type="submit">Duyệt đơn</button>
                <button name="status" value="rejected" class="rounded-xl bg-red-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-red-700" type="submit">Từ chối</button>
            </div>
        </form>
    @endif
</div>
@endsection
