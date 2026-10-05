@extends('layouts.app')
@section('title', 'Chi tiết đơn nghỉ')
@section('content')
<x-page-header eyebrow="HR / Đơn nghỉ" title="Chi tiết đơn nghỉ" description="Xem thông tin và xử lý yêu cầu của nhân viên.">
    <a href="{{ route('hr.leave-requests.index') }}" class="app-button-secondary">Quay lại</a>
</x-page-header>

<div class="app-panel max-w-3xl p-5 sm:p-6">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3 border-b border-[var(--app-border)] pb-5">
        <div>
            <p class="text-lg font-bold text-[var(--app-text)]">{{ $leaveRequest->employee?->user?->name ?? '—' }}</p>
            <p class="mt-1 text-sm text-[var(--app-muted)]">{{ $leaveRequest->employee?->employee_code }} · {{ $leaveRequest->employee?->department?->name ?? 'Chưa phân phòng ban' }}</p>
        </div>
        <div>
            <x-status-badge :status="$leaveRequest->status" :label="['pending'=>'Đang chờ','approved'=>'Đã duyệt','rejected'=>'Từ chối','cancelled'=>'Đã hủy'][$leaveRequest->status] ?? $leaveRequest->status" />
        </div>
    </div>

    <dl class="grid gap-5 sm:grid-cols-2">
        <div>
            <dt class="app-label">Loại nghỉ</dt>
            <dd class="mt-1 font-medium text-[var(--app-text)]">{{ ['annual'=>'Nghỉ phép năm','sick'=>'Nghỉ ốm','unpaid'=>'Nghỉ không lương','other'=>'Khác'][$leaveRequest->leave_type] ?? $leaveRequest->leave_type }}</dd>
        </div>
        <div>
            <dt class="app-label">Thời gian nghỉ</dt>
            <dd class="mt-1 font-medium text-[var(--app-text)]">{{ $leaveRequest->start_date->format('d/m/Y') }} - {{ $leaveRequest->end_date->format('d/m/Y') }} ({{ $leaveRequest->start_date->diffInDays($leaveRequest->end_date) + 1 }} ngày)</dd>
        </div>
        <div>
            <dt class="app-label">Ngày gửi đơn</dt>
            <dd class="mt-1 text-sm text-[var(--app-muted)]">{{ $leaveRequest->created_at->format('d/m/Y H:i') }}</dd>
        </div>
        <div>
            <dt class="app-label">Trạng thái hiện tại</dt>
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
                        <dt class="text-xs font-semibold uppercase tracking-wider text-emerald-700/80 dark:text-emerald-400">Ghi chú phê duyệt</dt>
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
                        <dd class="mt-1.5 italic text-sm text-[var(--app-muted)]">(Không có ghi chú kèm theo)</dd>
                    @endif
                </div>
            </dl>
        </div>
    @elseif($leaveRequest->status === 'cancelled')
        <div class="mt-6 rounded-2xl border border-slate-300 bg-slate-50/70 p-4 text-sm text-slate-600 dark:border-slate-700 dark:bg-slate-800/40 dark:text-slate-400">
            <span class="font-semibold">Trạng thái:</span> Đơn xin nghỉ đã được nhân viên chủ động hủy bỏ.
        </div>
    @endif

    {{-- Card Phân tích nhân sự --}}
    @if(isset($conflictAnalysis))
        @if(! $conflictAnalysis['has_department'])
            <div class="mt-6 rounded-2xl border border-slate-300 bg-slate-50/70 p-5 text-sm dark:border-slate-700 dark:bg-slate-800/40">
                <div class="flex items-center gap-2 text-slate-800 dark:text-slate-200">
                    <svg class="h-5 w-5 shrink-0 text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                    </svg>
                    <h3 class="text-base font-bold">Phân tích nhân sự</h3>
                </div>
                <p class="mt-2 text-sm text-[var(--app-muted)]">{{ $conflictAnalysis['message'] ?? 'Không thể phân tích do nhân viên chưa được phân bổ phòng ban.' }}</p>
            </div>
        @elseif($conflictAnalysis['total_active_employees'] === 0)
            <div class="mt-6 rounded-2xl border border-slate-300 bg-slate-50/70 p-5 text-sm dark:border-slate-700 dark:bg-slate-800/40">
                <div class="flex items-center gap-2 text-slate-800 dark:text-slate-200">
                    <h3 class="text-base font-bold">Phân tích nhân sự</h3>
                </div>
                <p class="mt-2 text-sm text-[var(--app-muted)]">{{ $conflictAnalysis['message'] }}</p>
            </div>
        @else
            @php
                $isDanger = $conflictAnalysis['highest_level'] === 'danger';
                $isWarning = $conflictAnalysis['highest_level'] === 'warning';
                $cardBorderClass = $isDanger ? 'border-rose-500/30 bg-rose-500/5 dark:bg-rose-950/20' : ($isWarning ? 'border-amber-500/30 bg-amber-500/5 dark:bg-amber-950/20' : 'border-emerald-500/30 bg-emerald-500/5 dark:bg-emerald-950/20');
                $titleColorClass = $isDanger ? 'text-rose-800 dark:text-rose-300' : ($isWarning ? 'text-amber-800 dark:text-amber-300' : 'text-emerald-800 dark:text-emerald-300');
                $badgeClass = $isDanger ? 'bg-rose-500/20 text-rose-700 dark:text-rose-300' : ($isWarning ? 'bg-amber-500/20 text-amber-700 dark:text-amber-300' : 'bg-emerald-500/20 text-emerald-700 dark:text-emerald-300');
            @endphp
            <div class="mt-6 rounded-2xl border p-5 text-sm {{ $cardBorderClass }}">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div class="flex items-center gap-2 {{ $titleColorClass }}">
                        @if($isDanger || $isWarning)
                            <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                            </svg>
                        @else
                            <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        @endif
                        <h3 class="text-base font-bold">Phân tích nhân sự</h3>
                    </div>
                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $badgeClass }}">
                        @if($isDanger)
                            Nguy cơ thiếu nhân sự
                        @elseif($isWarning)
                            Lưu ý về nhân sự
                        @else
                            Đảm bảo nhân sự
                        @endif
                    </span>
                </div>

                <div class="mt-3">
                    @if($isDanger)
                        <p class="font-semibold text-rose-700 dark:text-rose-300">
                            ⚠ Nguy cơ thiếu nhân sự
                        </p>
                        <p class="mt-1 text-sm text-[var(--app-text)]">
                            Nếu duyệt đơn này: Ngày <span class="font-bold">{{ $conflictAnalysis['highest_day']['formatted_date'] }}</span> sẽ có <span class="font-bold">{{ $conflictAnalysis['highest_day']['projected_leave_count'] }}/{{ $conflictAnalysis['highest_day']['total_employees'] }}</span> nhân viên phòng {{ $conflictAnalysis['department']['name'] }} nghỉ (<span class="font-bold">{{ $conflictAnalysis['highest_day']['percentage_formatted'] }}</span>).
                        </p>
                        <p class="mt-1 text-xs text-rose-700/80 dark:text-rose-300/80">
                            Việc duyệt đơn có thể ảnh hưởng đến khả năng bố trí nhân sự của phòng ban.
                        </p>
                    @elseif($isWarning)
                        <p class="font-semibold text-amber-700 dark:text-amber-300">
                            ⚠ Lưu ý về nhân sự
                        </p>
                        <p class="mt-1 text-sm text-[var(--app-text)]">
                            Nếu duyệt đơn này: Ngày <span class="font-bold">{{ $conflictAnalysis['highest_day']['formatted_date'] }}</span> sẽ có <span class="font-bold">{{ $conflictAnalysis['highest_day']['projected_leave_count'] }}/{{ $conflictAnalysis['highest_day']['total_employees'] }}</span> nhân viên phòng {{ $conflictAnalysis['department']['name'] }} nghỉ (<span class="font-bold">{{ $conflictAnalysis['highest_day']['percentage_formatted'] }}</span>).
                        </p>
                    @else
                        <p class="font-semibold text-emerald-700 dark:text-emerald-300">
                            ✓ Mức nhân sự của {{ $conflictAnalysis['department']['name'] }} vẫn đảm bảo trong thời gian nghỉ này.
                        </p>
                        @if($conflictAnalysis['highest_day'])
                            <p class="mt-1 text-sm text-[var(--app-muted)]">
                                Ngày có tỷ lệ nghỉ cao nhất: <span class="font-medium text-[var(--app-text)]">{{ $conflictAnalysis['highest_day']['formatted_date'] }}</span> — <span class="font-medium text-[var(--app-text)]">{{ $conflictAnalysis['highest_day']['projected_leave_count'] }}/{{ $conflictAnalysis['highest_day']['total_employees'] }} nhân viên ({{ $conflictAnalysis['highest_day']['percentage_formatted'] }})</span>
                            </p>
                        @endif
                    @endif
                </div>

                @if(count($conflictAnalysis['days']) > 1)
                    <div class="mt-4 border-t border-[var(--app-border)] pt-3.5">
                        <p class="mb-2 text-xs font-semibold uppercase tracking-wider text-[var(--app-muted)]">Bảng chi tiết từng ngày nghỉ</p>
                        <div class="overflow-x-auto rounded-xl border border-[var(--app-border)] bg-white/70 dark:bg-slate-900/60 shadow-xs">
                            <table class="w-full text-left text-xs sm:text-sm">
                                <thead>
                                    <tr class="border-b border-[var(--app-border)] bg-slate-50/70 font-semibold text-[var(--app-muted)] dark:bg-slate-800/60">
                                        <th class="px-3.5 py-2.5">Ngày</th>
                                        <th class="px-3.5 py-2.5 text-center">Đã nghỉ</th>
                                        <th class="px-3.5 py-2.5 text-center">Nếu duyệt</th>
                                        <th class="px-3.5 py-2.5 text-center">Tỷ lệ</th>
                                        <th class="px-3.5 py-2.5 text-right">Đánh giá</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-[var(--app-border)]">
                                    @foreach($conflictAnalysis['days'] as $day)
                                        <tr class="{{ $day['level'] === 'danger' ? 'bg-rose-500/10' : ($day['level'] === 'warning' ? 'bg-amber-500/10' : '') }}">
                                            <td class="px-3.5 py-2.5 font-medium text-[var(--app-text)] whitespace-nowrap">
                                                {{ $day['formatted_date'] }}
                                            </td>
                                            <td class="px-3.5 py-2.5 text-center text-[var(--app-muted)]">
                                                {{ $day['approved_leave_count'] }}
                                            </td>
                                            <td class="px-3.5 py-2.5 text-center font-medium text-[var(--app-text)]">
                                                {{ $day['projected_leave_count'] }}/{{ $day['total_employees'] }}
                                            </td>
                                            <td class="px-3.5 py-2.5 text-center font-bold {{ $day['level'] === 'danger' ? 'text-rose-600 dark:text-rose-400' : ($day['level'] === 'warning' ? 'text-amber-600 dark:text-amber-400' : 'text-emerald-600 dark:text-emerald-400') }}">
                                                {{ $day['percentage_formatted'] }}
                                            </td>
                                            <td class="px-3.5 py-2.5 text-right">
                                                @if($day['level'] === 'danger')
                                                    <span class="inline-flex items-center rounded-full bg-rose-500/20 px-2 py-0.5 text-xs font-semibold text-rose-700 dark:text-rose-300">Nguy cơ</span>
                                                @elseif($day['level'] === 'warning')
                                                    <span class="inline-flex items-center rounded-full bg-amber-500/20 px-2 py-0.5 text-xs font-semibold text-amber-700 dark:text-amber-300">Lưu ý</span>
                                                @else
                                                    <span class="inline-flex items-center rounded-full bg-emerald-500/20 px-2 py-0.5 text-xs font-semibold text-emerald-700 dark:text-emerald-300">Bình thường</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            </div>
        @endif
    @endif

    {{-- Review form for pending requests --}}
    @if($leaveRequest->status === 'pending')
        <div class="mt-6 rounded-2xl border border-amber-500/30 bg-amber-500/5 p-4 text-xs font-medium text-amber-800 dark:text-amber-300">
            Đơn đang chờ xem xét duyệt. Vui lòng kiểm tra lịch sử chấm công và lý do trước khi đưa ra quyết định.
        </div>

        <form method="POST" action="{{ route('hr.leave-requests.review', $leaveRequest) }}" class="mt-6 border-t border-[var(--app-border)] pt-5">
            @csrf
            @method('PATCH')
            <label for="review_note" class="app-label">Ghi chú xử lý / Lý do (nếu từ chối)</label>
            <textarea id="review_note" name="review_note" rows="3" class="app-input mt-2 w-full" placeholder="Nhập ghi chú phê duyệt hoặc lý do từ chối..."></textarea>
            @error('review_note')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
            <div class="mt-4 flex flex-wrap gap-3">
                <button name="status" value="approved" class="app-button-primary" type="submit">Duyệt đơn</button>
                <button name="status" value="rejected" class="rounded-xl bg-red-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-red-700" type="submit">Từ chối</button>
            </div>
        </form>
    @endif
</div>
@endsection
