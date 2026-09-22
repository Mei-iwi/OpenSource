@extends('layouts.app')

@section('title', 'Tạo kênh trò chuyện mới — Chat nội bộ')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    
    <!-- Top Bar -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('chat.index') }}" class="p-2 rounded-xl border border-[var(--app-border)] text-[var(--app-muted)] hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m15 18-6-6 6-6"/></svg>
            </a>
            <div>
                <h1 class="text-xl font-bold text-[var(--app-text)]">Tạo kênh trò chuyện mới</h1>
                <p class="text-xs text-[var(--app-muted)]">Thiết lập kênh trao đổi theo phòng ban hoặc theo nhóm dự án</p>
            </div>
        </div>
    </div>

    <!-- Form Card -->
    <div class="rounded-2xl border border-[var(--app-border)] bg-[var(--app-surface)] p-6 sm:p-8 shadow-sm">
        <form action="{{ route('chat.channels.store') }}" method="POST" class="space-y-6" x-data="{ channelType: '{{ old('type', 'department') }}', channelName: '{{ old('name', '') }}', channelSlug: '{{ old('slug', '') }}', autoSlug: true, generateSlug() { if (this.autoSlug) { this.channelSlug = this.channelName.toLowerCase().trim().replace(/[^\w\s-]/g, '').replace(/[\s_-]+/g, '-').replace(/^-+|-+$/g, ''); } } }">
            @csrf

            <!-- Loại kênh -->
            <div>
                <label class="block text-sm font-bold text-[var(--app-text)] mb-2">Loại kênh <span class="text-rose-500">*</span></label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <label class="relative flex cursor-pointer rounded-xl border p-4 transition-all"
                           :class="channelType === 'department' ? 'border-indigo-600 bg-indigo-50/20 dark:bg-indigo-950/20 ring-2 ring-indigo-500/20' : 'border-[var(--app-border)] hover:bg-slate-50 dark:hover:bg-slate-800/40'">
                        <input type="radio" name="type" value="department" x-model="channelType" class="sr-only">
                        <div class="flex items-center gap-3">
                            <span class="text-2xl">🏢</span>
                            <div>
                                <span class="block text-sm font-bold text-[var(--app-text)]">Kênh Phòng Ban</span>
                                <span class="block text-xs text-[var(--app-muted)]">Dành riêng cho nhân sự thuộc một phòng ban cụ thể</span>
                            </div>
                        </div>
                    </label>

                    <label class="relative flex cursor-pointer rounded-xl border p-4 transition-all"
                           :class="channelType === 'group' ? 'border-indigo-600 bg-indigo-50/20 dark:bg-indigo-950/20 ring-2 ring-indigo-500/20' : 'border-[var(--app-border)] hover:bg-slate-50 dark:hover:bg-slate-800/40'">
                        <input type="radio" name="type" value="group" x-model="channelType" class="sr-only">
                        <div class="flex items-center gap-3">
                            <span class="text-2xl">👥</span>
                            <div>
                                <span class="block text-sm font-bold text-[var(--app-text)]">Kênh Nhóm / Dự Án</span>
                                <span class="block text-xs text-[var(--app-muted)]">Kênh linh hoạt giữa các cá nhân được chỉ định</span>
                            </div>
                        </div>
                    </label>
                </div>
                @error('type')
                    <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Phòng ban liên kết (hiển thị khi loại kênh là phòng ban) -->
            <div x-show="channelType === 'department'" x-transition>
                <label for="department_id" class="block text-sm font-bold text-[var(--app-text)] mb-1">
                    Phòng ban trực thuộc <span class="text-rose-500">*</span>
                </label>
                <select id="department_id" name="department_id" class="w-full rounded-xl border border-[var(--app-border)] bg-[var(--app-surface)] px-4 py-2.5 text-sm text-[var(--app-text)] focus:ring-2 focus:ring-indigo-500/40">
                    <option value="">-- Chọn phòng ban --</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" {{ old('department_id') == $dept->id ? 'selected' : '' }}>
                            {{ $dept->name }} ({{ $dept->code }})
                        </option>
                    @endforeach
                </select>
                <p class="mt-1 text-xs text-[var(--app-muted)]">Nếu để trống danh sách thành viên bên dưới, toàn bộ nhân sự phòng ban này sẽ được tự động thêm vào kênh.</p>
                @error('department_id')
                    <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Tên kênh & Slug -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="name" class="block text-sm font-bold text-[var(--app-text)] mb-1">
                        Tên kênh <span class="text-rose-500">*</span>
                    </label>
                    <input type="text"
                           id="name"
                           name="name"
                           x-model="channelName"
                           @input="generateSlug()"
                           value="{{ old('name') }}"
                           placeholder="Ví dụ: Kế toán, Dự án Web, Ban Giám Đốc"
                           class="w-full rounded-xl border border-[var(--app-border)] bg-[var(--app-surface)] px-4 py-2.5 text-sm text-[var(--app-text)] focus:ring-2 focus:ring-indigo-500/40">
                    @error('name')
                        <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="slug" class="block text-sm font-bold text-[var(--app-text)] mb-1">
                        Mã định danh slug <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute left-3.5 top-2.5 text-sm text-[var(--app-muted)] font-mono">#</span>
                        <input type="text"
                               id="slug"
                               name="slug"
                               x-model="channelSlug"
                               @input="autoSlug = false"
                               value="{{ old('slug') }}"
                               placeholder="ke-toan"
                               class="w-full pl-8 pr-4 py-2.5 font-mono text-sm rounded-xl border border-[var(--app-border)] bg-[var(--app-surface)] text-[var(--app-text)] focus:ring-2 focus:ring-indigo-500/40">
                    </div>
                    @error('slug')
                        <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Mô tả -->
            <div>
                <label for="description" class="block text-sm font-bold text-[var(--app-text)] mb-1">Mô tả mục đích kênh</label>
                <textarea id="description"
                          name="description"
                          rows="2"
                          placeholder="Mô tả ngắn về chủ đề trao đổi trong kênh này..."
                          class="w-full rounded-xl border border-[var(--app-border)] bg-[var(--app-surface)] px-4 py-2.5 text-sm text-[var(--app-text)] focus:ring-2 focus:ring-indigo-500/40">{{ old('description') }}</textarea>
                @error('description')
                    <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Thêm thành viên ban đầu -->
            <div>
                <label class="block text-sm font-bold text-[var(--app-text)] mb-1">Thành viên ban đầu</label>
                <select name="members[]" multiple class="w-full h-36 rounded-xl border border-[var(--app-border)] bg-[var(--app-surface)] px-4 py-2 text-xs text-[var(--app-text)] focus:ring-2 focus:ring-indigo-500/40">
                    @foreach($employees as $emp)
                        <option value="{{ $emp->id }}" {{ in_array($emp->id, old('members', [])) ? 'selected' : '' }}>
                            {{ $emp->name }} — {{ $emp->employee?->department?->name ?? 'Không phòng ban' }} ({{ $emp->role }})
                        </option>
                    @endforeach
                </select>
                <p class="mt-1 text-xs text-[var(--app-muted)]">Giữ Ctrl / Cmd để chọn nhiều nhân sự. Bạn cũng có thể thêm hoặc xóa thành viên bất kỳ lúc nào sau khi tạo kênh.</p>
                @error('members')
                    <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Buttons -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-[var(--app-border)]">
                <a href="{{ route('chat.index') }}" class="px-5 py-2.5 rounded-xl border border-[var(--app-border)] text-sm font-semibold text-[var(--app-text)] hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                    Hủy bỏ
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold shadow-lg shadow-indigo-600/30 transition">
                    Tạo kênh ngay
                </button>
            </div>

        </form>
    </div>

</div>
@endsection
