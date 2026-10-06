@extends('layouts.app')
@section('title', 'Gửi đơn xin nghỉ')
@section('content')
<x-page-header eyebrow="Cá nhân" title="Gửi đơn xin nghỉ" description="Điền thông tin khoảng thời gian và lý do nghỉ.">
    <a href="{{ route('employee.leave-requests.index') }}" class="app-button-secondary">Quay lại danh sách</a>
</x-page-header>

@if(!empty($leaveBalance))
    <div class="app-panel mb-6 max-w-3xl p-4 sm:p-5 border-l-4 border-l-emerald-500 bg-emerald-500/5 dark:bg-emerald-950/20">
        <div class="flex items-center justify-between gap-2">
            <div>
                <p class="font-bold text-emerald-900 dark:text-emerald-200">Quỹ nghỉ phép năm {{ $leaveBalance['year'] }} của bạn</p>
                <p class="mt-1 text-xs text-[var(--app-muted)]">
                    Tổng được cấp: <strong class="text-[var(--app-text)]">{{ $leaveBalance['entitlement'] }}</strong> ngày |
                    Đã sử dụng: <strong class="text-[var(--app-text)]">{{ $leaveBalance['used_days'] }}</strong> ngày |
                    Đang chờ duyệt: <strong class="text-[var(--app-text)]">{{ $leaveBalance['pending_days'] }}</strong> ngày
                </p>
            </div>
            <div class="text-right shrink-0">
                <span class="text-xs font-semibold uppercase tracking-wider text-emerald-700 dark:text-emerald-400">Khả dụng</span>
                <p class="text-xl font-black text-emerald-700 dark:text-emerald-300">{{ $leaveBalance['available_days'] }} <span class="text-xs font-normal">ngày</span></p>
            </div>
        </div>
    </div>
@endif

<div class="app-panel max-w-3xl p-5 sm:p-6">
    <form method="POST" action="{{ route('employee.leave-requests.store') }}">
        @csrf
        <div class="grid gap-5 sm:grid-cols-2">
            <div>
                <label for="leave_type" class="app-label">Loại nghỉ <span class="text-red-600">*</span></label>
                <select id="leave_type" name="leave_type" class="app-input mt-2 w-full">
                    <option value="">Chọn loại nghỉ</option>
                    @foreach(['annual'=>'Nghỉ phép năm','sick'=>'Nghỉ ốm','unpaid'=>'Nghỉ không lương','other'=>'Khác'] as $value=>$label)
                        <option value="{{ $value }}" @selected(old('leave_type') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('leave_type')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div></div>

            <div>
                <label for="start_date" class="app-label">Từ ngày <span class="text-red-600">*</span></label>
                <input id="start_date" name="start_date" type="date" value="{{ old('start_date') }}" class="app-input mt-2 w-full">
                @error('start_date')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="end_date" class="app-label">Đến ngày <span class="text-red-600">*</span></label>
                <input id="end_date" name="end_date" type="date" value="{{ old('end_date') }}" class="app-input mt-2 w-full">
                @error('end_date')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="sm:col-span-2">
                <label for="reason" class="app-label">Lý do <span class="text-red-600">*</span></label>
                <textarea id="reason" name="reason" rows="5" class="app-input mt-2 w-full" placeholder="Mô tả lý do xin nghỉ...">{{ old('reason') }}</textarea>
                @error('reason')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="mt-6 flex gap-3">
            <button type="submit" class="app-button-primary">Gửi đơn</button>
            <a href="{{ route('employee.leave-requests.index') }}" class="app-button-secondary">Hủy</a>
        </div>
    </form>
</div>
@endsection
