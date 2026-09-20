@extends('layouts.app')

@php
    $section = old('_section', request('section'));
    $communicationTitle = match ($section) {
        'mail' => 'Thư',
        'advertisement' => 'Quảng cáo',
        default => 'Thư và quảng cáo',
    };
@endphp
@section('title', $communicationTitle)

@section('content')
<div @class(['min-h-full flex flex-col justify-center' => !in_array($section, ['mail', 'advertisement'], true)])>
<x-page-header :title="$communicationTitle" />

@if(!in_array($section, ['mail', 'advertisement'], true))
<div class="mx-auto mb-6 grid w-full max-w-3xl gap-4 sm:grid-cols-2">
    <a href="{{ route('admin.communications.index', ['section' => 'mail']) }}" class="directory-card flex min-h-44 flex-col items-center justify-center p-6 text-center">
        <svg class="h-7 w-7 text-blue-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="3"/><path d="m3 7 9 6 9-6"/></svg><h2 class="mt-2 text-lg font-bold">Thư nội bộ</h2><p class="app-subtitle">Soạn thư, chọn người nhận và xem lịch sử gửi.</p>
    </a>
    <a href="{{ route('admin.communications.index', ['section' => 'advertisement']) }}" class="directory-card flex min-h-44 flex-col items-center justify-center p-6 text-center">
        <svg class="h-7 w-7 text-blue-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="3"/><path d="m3 16 6-6 12 9"/><circle cx="16" cy="8" r="2"/></svg><h2 class="mt-2 text-lg font-bold">Quảng cáo</h2><p class="app-subtitle">Chọn hình ảnh, nội dung và lịch hiển thị.</p>
    </a>
</div>
@endif
<div class="mx-auto max-w-3xl">
    @if($section === 'mail')
    <section class="app-panel p-5 sm:p-6">
        <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
            <p class="app-subtitle">Chỉ Admin có thể gửi thư đến HR và Employee.</p>
            <a href="{{ route('admin.communications.history') }}" class="app-button-secondary">Xem lịch sử thư</a>
        </div>
        <form method="POST" action="{{ route('admin.communications.messages.send') }}" class="space-y-4" x-data="{ audience: @js(old('audience', 'all')) }">
            @csrf
            <input type="hidden" name="_section" value="{{ $section }}">
            <div>
                <label for="subject" class="app-label">Tiêu đề</label>
                <input id="subject" name="subject" value="{{ old('subject') }}" required maxlength="180" class="app-input mt-2 w-full" placeholder="Ví dụ: Lịch chấm công cuối tháng">
                @error('subject')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="body" class="app-label">Nội dung</label>
                <textarea id="body" name="body" rows="7" required maxlength="10000" class="app-input mt-2 w-full" placeholder="Nhập nội dung thư...">{{ old('body') }}</textarea>
                @error('body')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="audience" class="app-label">Người nhận</label>
                <select id="audience" name="audience" x-model="audience" class="app-input mt-2 w-full">
                    <option value="all">Tất cả HR và Employee</option>
                    <option value="hr">Chỉ HR</option>
                    <option value="employee">Chỉ Employee</option>
                    <option value="selected">Chọn từng tài khoản</option>
                </select>
                @error('audience')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
            </div>
            <div x-show="audience === 'selected'" x-cloak class="rounded-xl border border-[var(--app-border)] p-3">
                <p class="mb-2 text-xs font-bold uppercase tracking-wider text-[var(--app-muted)]">Danh sách chọn</p>
                <div class="max-h-52 space-y-2 overflow-y-auto">
                    @foreach($recipients as $recipient)
                        <label class="flex items-center gap-2 text-sm text-[var(--app-text)]">
                            <input type="checkbox" name="recipient_ids[]" value="{{ $recipient->id }}" @checked(in_array($recipient->id, old('recipient_ids', []))) class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                            <span>{{ $recipient->name }} · {{ $recipient->email }} ({{ strtoupper($recipient->role) }})</span>
                        </label>
                    @endforeach
                </div>
                @error('recipient_ids')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
            </div>
            <button type="submit" class="app-button-primary w-full sm:w-auto">Gửi thư</button>
        </form>
    </section>

    @elseif($section === 'advertisement')
    <section class="app-panel p-5 sm:p-6">
        <div class="mb-5 flex items-start justify-between gap-4">
            <div>
                <p class="app-subtitle">Hiển thị lặp lại trên tài khoản HR và Employee khi được bật.</p>
            </div>
            @if($advertisement?->is_active)
                <span class="rounded-full border border-emerald-500/20 bg-emerald-500/10 px-2.5 py-1 text-xs font-bold text-emerald-700 dark:text-emerald-300">Đang bật</span>
            @else
                <span class="rounded-full border border-slate-300 bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-600 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300">Đang tắt</span>
            @endif
        </div>
        <form method="POST" action="{{ route('admin.communications.advertisement.save') }}" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <input type="hidden" name="_section" value="{{ $section }}">
            <div>
                <label for="ad-title" class="app-label">Tiêu đề quảng cáo</label>
                <input id="ad-title" name="title" value="{{ old('title', $advertisement?->title ?? 'Tin vui từ ban lãnh đạo') }}" required maxlength="160" class="app-input mt-2 w-full">
                @error('title')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="ad-message" class="app-label">Nội dung</label>
                <textarea id="ad-message" name="message" rows="7" required maxlength="5000" class="app-input mt-2 w-full">{{ old('message', $advertisement?->message ?? 'Nhờ tinh thần cống hiến không ngừng của đội ngũ nhân viên, doanh thu công ty vẫn duy trì đà tăng trưởng ấn tượng theo hướng âm. Để cải thiện tình hình, công ty đã nhanh chóng triển khai chiến dịch quảng cáo sản phẩm đến chính nhân viên, biến người lao động từ lực lượng tạo ra doanh thu thành lực lượng trực tiếp đóng góp doanh thu bằng cách mua sản phẩm của công ty mình.') }}</textarea>
                @error('message')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
            </div>
            <x-image-picker name="image" id="ad-image" label="Ảnh quảng cáo" shape="rectangle" :max-mb="4" :src="$advertisement?->image_path ? route('advertisement.image', $advertisement) : null" />
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="display-seconds" class="app-label">Thời gian hiển thị (giây)</label>
                    <input id="display-seconds" name="display_seconds" type="number" min="5" max="300" value="{{ old('display_seconds', $advertisement?->display_seconds ?? 10) }}" required class="app-input mt-2 w-full">
                </div>
                <div>
                    <label for="repeat-seconds" class="app-label">Lặp lại sau (giây)</label>
                    <input id="repeat-seconds" name="repeat_seconds" type="number" min="10" max="3600" value="{{ old('repeat_seconds', $advertisement?->repeat_seconds ?? 60) }}" required class="app-input mt-2 w-full">
                </div>
            </div>
            @error('display_seconds')<p class="text-sm text-rose-600">{{ $message }}</p>@enderror
            @error('repeat_seconds')<p class="text-sm text-rose-600">{{ $message }}</p>@enderror
            <label class="flex items-center gap-2 text-sm font-semibold text-[var(--app-text)]">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $advertisement?->is_active ?? false)) class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                Kích hoạt ngay sau khi lưu
            </label>
            <button type="submit" class="app-button-primary w-full sm:w-auto">Lưu quảng cáo</button>
        </form>
        @if($advertisement)
            <form method="POST" action="{{ route('admin.communications.advertisement.toggle', $advertisement) }}" class="mt-3">
                @csrf @method('PATCH')
                <button type="submit" class="app-button-secondary w-full sm:w-auto">{{ $advertisement->is_active ? 'Tắt quảng cáo' : 'Bật quảng cáo' }}</button>
            </form>
        @endif
        <div class="mt-5 rounded-xl border border-amber-400/30 bg-amber-500/10 p-3 text-xs leading-relaxed text-amber-700 dark:text-amber-300">
            Gợi ý: Nội dung ngắn gọn, hình ảnh rõ nét và thời gian lặp phù hợp sẽ giúp thông báo dễ đọc hơn.
        </div>
    </section>
    @else
        <p class="py-8 text-center text-[var(--app-muted)]">Chọn Thư nội bộ hoặc Quảng cáo để bắt đầu.</p>
    @endif
</div>

</div>
@endsection
