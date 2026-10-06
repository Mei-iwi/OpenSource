@extends('layouts.app')
@section('title', 'Đơn nghỉ của tôi')
@section('content')
<x-page-header eyebrow="Cá nhân" title="Đơn nghỉ của tôi" description="Theo dõi các đơn xin nghỉ của bạn.">
    @if($hasEmployeeProfile ?? true)
        <a href="{{ route('employee.leave-requests.create') }}" class="app-button-primary">Gửi đơn xin nghỉ</a>
    @endif
</x-page-header>

@if(! ($hasEmployeeProfile ?? true))
    <div class="app-panel mb-6 border-l-4 border-l-amber-500 bg-amber-500/5 p-4 text-sm text-amber-800 dark:text-amber-300">
        <p class="font-bold">Tài khoản chưa liên kết hồ sơ nhân viên</p>
        <p class="mt-1 text-xs text-[var(--app-muted)]">Bạn đang đăng nhập bằng tài khoản quản trị/nhân sự chưa có hồ sơ nhân viên tương ứng. Vui lòng liên kết hồ sơ nhân viên trước khi gửi đơn nghỉ cá nhân.</p>
    </div>
@elseif(!empty($leaveBalance))
    <div class="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-2xl border border-[var(--app-border)] bg-[var(--app-surface)] p-4 shadow-sm">
            <div class="flex items-center justify-between text-xs font-semibold uppercase tracking-wider text-[var(--app-muted)]">
                <span>Tổng phép năm {{ $leaveBalance['year'] }}</span>
                <span class="rounded-full bg-blue-500/10 px-2 py-0.5 text-[10px] font-bold text-blue-600 dark:text-blue-400">Được cấp</span>
            </div>
            <p class="mt-2 text-2xl font-bold text-[var(--app-text)]">{{ $leaveBalance['entitlement'] }} <span class="text-xs font-normal text-[var(--app-muted)]">ngày</span></p>
            @if($leaveBalance['carried_over_days'] > 0)
                <p class="mt-1 text-[11px] text-[var(--app-muted)]">+{{ $leaveBalance['carried_over_days'] }} ngày chuyển từ năm trước</p>
            @else
                <p class="mt-1 text-[11px] text-[var(--app-muted)]">Gốc: {{ $leaveBalance['total_days'] }} ngày</p>
            @endif
        </div>

        <div class="rounded-2xl border border-[var(--app-border)] bg-[var(--app-surface)] p-4 shadow-sm">
            <div class="flex items-center justify-between text-xs font-semibold uppercase tracking-wider text-[var(--app-muted)]">
                <span>Đã sử dụng</span>
                <span class="rounded-full bg-slate-500/10 px-2 py-0.5 text-[10px] font-bold text-slate-600 dark:text-slate-400">Đã duyệt</span>
            </div>
            <p class="mt-2 text-2xl font-bold text-[var(--app-text)]">{{ $leaveBalance['used_days'] }} <span class="text-xs font-normal text-[var(--app-muted)]">ngày</span></p>
            <p class="mt-1 text-[11px] text-[var(--app-muted)]">Đã tính vào công nghỉ phép</p>
        </div>

        <div class="rounded-2xl border border-[var(--app-border)] bg-[var(--app-surface)] p-4 shadow-sm">
            <div class="flex items-center justify-between text-xs font-semibold uppercase tracking-wider text-[var(--app-muted)]">
                <span>Đang chờ duyệt</span>
                <span class="rounded-full bg-amber-500/10 px-2 py-0.5 text-[10px] font-bold text-amber-600 dark:text-amber-400">Tạm giữ</span>
            </div>
            <p class="mt-2 text-2xl font-bold text-amber-600 dark:text-amber-400">{{ $leaveBalance['pending_days'] }} <span class="text-xs font-normal text-[var(--app-muted)]">ngày</span></p>
            <p class="mt-1 text-[11px] text-[var(--app-muted)]">Đang giữ quota theo đơn chờ</p>
        </div>

        <div class="rounded-2xl border border-emerald-500/30 bg-emerald-500/5 p-4 shadow-sm dark:bg-emerald-950/20">
            <div class="flex items-center justify-between text-xs font-semibold uppercase tracking-wider text-emerald-800 dark:text-emerald-300">
                <span>Khả dụng còn lại</span>
                <span class="rounded-full bg-emerald-500/20 px-2 py-0.5 text-[10px] font-bold text-emerald-700 dark:text-emerald-300">Có thể xin</span>
            </div>
            <p class="mt-2 text-2xl font-bold text-emerald-700 dark:text-emerald-400">{{ $leaveBalance['available_days'] }} <span class="text-xs font-normal text-emerald-600/70 dark:text-emerald-400/70">ngày</span></p>
            <p class="mt-1 text-[11px] text-emerald-700/80 dark:text-emerald-400/80">Quỹ còn lại trừ đơn chờ duyệt</p>
        </div>
    </div>
@endif

<div class="app-panel overflow-hidden">
    <div class="overflow-x-auto">
        <table class="app-table">
            <thead>
                <tr>
                    <th>Loại nghỉ</th>
                    <th>Thời gian</th>
                    <th>Số ngày</th>
                    <th>Trạng thái</th>
                    <th>Ngày gửi</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($requests as $request)
                    <tr>
                        <td>{{ ['annual'=>'Nghỉ phép năm','sick'=>'Nghỉ ốm','unpaid'=>'Nghỉ không lương','other'=>'Khác'][$request->leave_type] ?? $request->leave_type }}</td>
                        <td>{{ $request->start_date->format('d/m/Y') }} - {{ $request->end_date->format('d/m/Y') }}</td>
                        <td>{{ $request->start_date->diffInDays($request->end_date) + 1 }}</td>
                        <td><x-status-badge :status="$request->status" :label="['pending'=>'Đang chờ','approved'=>'Đã duyệt','rejected'=>'Từ chối','cancelled'=>'Đã hủy'][$request->status] ?? $request->status" /></td>
                        <td>{{ $request->created_at->format('d/m/Y') }}</td>
                        <td class="text-right">
                            <a href="{{ route('employee.leave-requests.show', $request) }}" class="font-semibold text-orange-600 hover:text-orange-700">Chi tiết</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="py-12 text-center text-[var(--app-muted)]">Chưa có đơn xin nghỉ.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-5">
        {{ $requests->links() }}
    </div>
</div>
@endsection
