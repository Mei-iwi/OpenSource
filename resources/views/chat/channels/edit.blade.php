@extends('layouts.app')

@section('title', 'Cài đặt kênh #' . $channel->slug . ' — Chat nội bộ')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    
    <!-- Top Bar -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('chat.channels.show', $channel->slug) }}" class="p-2 rounded-xl border border-[var(--app-border)] text-[var(--app-muted)] hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m15 18-6-6 6-6"/></svg>
            </a>
            <div>
                <h1 class="text-xl font-bold text-[var(--app-text)]">Cài đặt kênh #{{ $channel->slug }}</h1>
                <p class="text-xs text-[var(--app-muted)]">Chỉnh sửa thông tin kênh và quản trị quyền truy cập</p>
            </div>
        </div>
    </div>

    <!-- Edit Form Card -->
    <div class="rounded-2xl border border-[var(--app-border)] bg-[var(--app-surface)] p-6 sm:p-8 shadow-sm">
        <form action="{{ route('chat.channels.update', $channel->slug) }}" method="POST" class="space-y-6">
            @csrf
            @method('PATCH')

            <!-- Tên kênh -->
            <div>
                <label for="name" class="block text-sm font-bold text-[var(--app-text)] mb-1">
                    Tên hiển thị <span class="text-rose-500">*</span>
                </label>
                <input type="text"
                       id="name"
                       name="name"
                       value="{{ old('name', $channel->name) }}"
                       class="w-full rounded-xl border border-[var(--app-border)] bg-[var(--app-surface)] px-4 py-2.5 text-sm text-[var(--app-text)] focus:ring-2 focus:ring-indigo-500/40">
                @error('name')
                    <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Slug (chỉ xem, không cho đổi để tránh hỏng link và IDOR) -->
            <div>
                <label class="block text-sm font-bold text-[var(--app-text)] mb-1">Mã định danh slug</label>
                <input type="text"
                       disabled
                       value="#{{ $channel->slug }}"
                       class="w-full rounded-xl border border-[var(--app-border)] bg-slate-100 dark:bg-slate-800/60 px-4 py-2.5 text-sm font-mono text-[var(--app-muted)] cursor-not-allowed">
            </div>

            <!-- Phòng ban liên kết -->
            @if($channel->type === 'department')
                <div>
                    <label for="department_id" class="block text-sm font-bold text-[var(--app-text)] mb-1">Phòng ban liên kết</label>
                    <select id="department_id" name="department_id" class="w-full rounded-xl border border-[var(--app-border)] bg-[var(--app-surface)] px-4 py-2.5 text-sm text-[var(--app-text)] focus:ring-2 focus:ring-indigo-500/40">
                        <option value="">-- Không liên kết --</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" {{ old('department_id', $channel->department_id) == $dept->id ? 'selected' : '' }}>
                                {{ $dept->name }} ({{ $dept->code }})
                            </option>
                        @endforeach
                    </select>
                    @error('department_id')
                        <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                    @enderror
                </div>
            @endif

            <!-- Mô tả -->
            <div>
                <label for="description" class="block text-sm font-bold text-[var(--app-text)] mb-1">Mô tả kênh</label>
                <textarea id="description"
                          name="description"
                          rows="3"
                          class="w-full rounded-xl border border-[var(--app-border)] bg-[var(--app-surface)] px-4 py-2.5 text-sm text-[var(--app-text)] focus:ring-2 focus:ring-indigo-500/40">{{ old('description', $channel->description) }}</textarea>
                @error('description')
                    <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Buttons -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-[var(--app-border)]">
                <a href="{{ route('chat.channels.show', $channel->slug) }}" class="px-5 py-2.5 rounded-xl border border-[var(--app-border)] text-sm font-semibold text-[var(--app-text)] hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                    Hủy
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold shadow-lg shadow-indigo-600/30 transition">
                    Lưu thay đổi
                </button>
            </div>
        </form>
    </div>

    <!-- Danger Zone: Xóa kênh (nếu không phải kênh mặc định) -->
    @if(!$channel->isCompany() && auth()->user()->can('delete', $channel))
        <div class="rounded-2xl border border-rose-300 dark:border-rose-900/60 bg-rose-50/50 dark:bg-rose-950/20 p-6 shadow-sm">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h3 class="text-sm font-bold text-rose-600 dark:text-rose-400">Xóa kênh trò chuyện</h3>
                    <p class="text-xs text-rose-600/80 dark:text-rose-300/70 mt-1">Toàn bộ tin nhắn và lịch sử trao đổi trong kênh này sẽ bị xóa khỏi hệ thống.</p>
                </div>
                <form action="{{ route('chat.channels.destroy', $channel->slug) }}" method="POST" onsubmit="return confirm('CẢNH BÁO: Bạn có chắc chắn muốn xóa kênh này? Thao tác không thể hoàn tác!')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold shadow transition">
                        Xóa kênh vĩnh viễn
                    </button>
                </form>
            </div>
        </div>
    @endif

</div>
@endsection
