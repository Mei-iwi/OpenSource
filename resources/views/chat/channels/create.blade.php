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
    <div class="rounded-2xl border border-[var(--app-border)] bg-[var(--app-surface)] p-6 sm:p-8 shadow-sm"
         x-data="{
             channelType: '{{ old('type', 'department') }}',
             channelName: '{{ old('name', '') }}',
             channelSlug: '{{ old('slug', '') }}',
             autoSlug: true,
             selectedDept: '{{ old('department_id', '') }}',
             empSearch: '',
             selectedMembers: {{ json_encode(array_map('intval', old('members', []))) }},
             employees: {{ Js::from($employees->map(fn($e) => [
                 'id' => $e->id,
                 'name' => $e->name,
                 'email' => $e->email,
                 'department_id' => $e->employee?->department_id,
                 'department_name' => $e->employee?->department?->name ?? 'Không phòng ban',
                 'position' => $e->employee?->position ?? 'Nhân viên',
             ])) }},
             generateSlug() {
                 if (this.autoSlug) {
                     this.channelSlug = this.channelName.toLowerCase().trim()
                         .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
                         .replace(/[đĐ]/g, 'd')
                         .replace(/[^\w\s-]/g, '')
                         .replace(/[\s_-]+/g, '-')
                         .replace(/^-+|-+$/g, '');
                 }
             },
             get filteredEmployees() {
                 let list = this.employees;
                 if (this.channelType === 'department') {
                     if (!this.selectedDept) return [];
                     list = list.filter(e => e.department_id == this.selectedDept);
                 }
                 if (this.empSearch.trim()) {
                     const q = this.empSearch.toLowerCase().trim();
                     list = list.filter(e => e.name.toLowerCase().includes(q) || e.email.toLowerCase().includes(q) || (e.department_name && e.department_name.toLowerCase().includes(q)));
                 }
                 return list;
             },
             selectAll() {
                 const currentFilteredIds = this.filteredEmployees.map(e => e.id);
                 const set = new Set([...this.selectedMembers, ...currentFilteredIds]);
                 this.selectedMembers = Array.from(set);
             },
             deselectAll() {
                 const currentFilteredIds = new Set(this.filteredEmployees.map(e => e.id));
                 this.selectedMembers = this.selectedMembers.filter(id => !currentFilteredIds.has(id));
             }
         }">
        <form action="{{ route('chat.channels.store') }}" method="POST" class="space-y-6">
            @csrf

            <!-- Loại kênh -->
            <div>
                <label class="block text-sm font-bold text-[var(--app-text)] mb-2">Loại kênh <span class="text-rose-500">*</span></label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <label class="relative flex cursor-pointer rounded-xl border p-4 transition-all"
                           :class="channelType === 'department' ? 'border-indigo-600 bg-indigo-50/20 dark:bg-indigo-950/20 ring-2 ring-indigo-500/20' : 'border-[var(--app-border)] hover:bg-slate-50 dark:hover:bg-slate-800/40'">
                        <input type="radio" name="type" value="department" x-model="channelType" class="sr-only" @change="selectedMembers = []">
                        <div class="flex items-center gap-3">
                            <div class="h-10 w-10 rounded-xl bg-indigo-500/10 flex items-center justify-center text-indigo-600 dark:text-indigo-400 shrink-0">
                                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 22V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v18Z"/><path d="M6 12H4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h2"/><path d="M18 9h2a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2h-2"/><path d="M10 6h4"/><path d="M10 10h4"/><path d="M10 14h4"/><path d="M10 18h4"/></svg>
                            </div>
                            <div>
                                <span class="block text-sm font-bold text-[var(--app-text)]">Kênh Phòng Ban</span>
                                <span class="block text-xs text-[var(--app-muted)]">Chỉ nhân sự thuộc phòng ban được chọn</span>
                            </div>
                        </div>
                    </label>

                    <label class="relative flex cursor-pointer rounded-xl border p-4 transition-all"
                           :class="channelType === 'group' ? 'border-indigo-600 bg-indigo-50/20 dark:bg-indigo-950/20 ring-2 ring-indigo-500/20' : 'border-[var(--app-border)] hover:bg-slate-50 dark:hover:bg-slate-800/40'">
                        <input type="radio" name="type" value="group" x-model="channelType" class="sr-only">
                        <div class="flex items-center gap-3">
                            <div class="h-10 w-10 rounded-xl bg-violet-500/10 flex items-center justify-center text-violet-600 dark:text-violet-400 shrink-0">
                                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                            </div>
                            <div>
                                <span class="block text-sm font-bold text-[var(--app-text)]">Kênh Nhóm / Dự Án</span>
                                <span class="block text-xs text-[var(--app-muted)]">Tự do mời nhân sự từ bất kỳ phòng ban nào</span>
                            </div>
                        </div>
                    </label>
                </div>
                @error('type')
                    <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Phòng ban liên kết -->
            <div x-show="channelType === 'department'" x-transition>
                <label for="department_id" class="block text-sm font-bold text-[var(--app-text)] mb-1">
                    Phòng ban trực thuộc <span class="text-rose-500">*</span>
                </label>
                <select id="department_id" name="department_id" x-model="selectedDept" @change="selectedMembers = []" class="w-full rounded-xl border border-[var(--app-border)] bg-[var(--app-surface)] px-4 py-2.5 text-sm text-[var(--app-text)] focus:ring-2 focus:ring-indigo-500/40">
                    <option value="">-- Chọn phòng ban --</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" {{ old('department_id') == $dept->id ? 'selected' : '' }}>
                            {{ $dept->name }} ({{ $dept->code }})
                        </option>
                    @endforeach
                </select>
                <p class="mt-1 text-xs text-[var(--app-muted)]">Hệ thống sẽ lọc danh sách nhân sự chỉ thuộc phòng ban này. Nếu không chọn riêng lẻ, mặc định toàn bộ phòng ban sẽ được tham gia.</p>
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

            <!-- Thành viên ban đầu với bộ lọc thông minh -->
            <div class="space-y-2">
                <div class="flex items-center justify-between">
                    <label class="block text-sm font-bold text-[var(--app-text)]">
                        Thành viên ban đầu
                        <span class="text-xs font-normal text-[var(--app-muted)]" x-text="'(' + selectedMembers.length + ' đã chọn)'"></span>
                    </label>
                    <div class="flex items-center gap-2">
                        <button type="button" @click="selectAll()" class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:underline">
                            Chọn tất cả
                        </button>
                        <span class="text-[var(--app-muted)]">•</span>
                        <button type="button" @click="deselectAll()" class="text-xs font-semibold text-slate-500 hover:underline">
                            Bỏ chọn
                        </button>
                    </div>
                </div>

                <!-- Ô tìm kiếm nhân sự -->
                <div class="relative">
                    <input type="text"
                           x-model="empSearch"
                           placeholder="Tìm theo tên hoặc email..."
                           class="w-full rounded-xl border border-[var(--app-border)] bg-[var(--app-surface)] pl-9 pr-4 py-2 text-xs text-[var(--app-text)] focus:ring-2 focus:ring-indigo-500/40">
                    <svg class="w-4 h-4 absolute left-3 top-2.5 text-[var(--app-muted)]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                </div>

                <!-- Danh sách nhân sự dạng checkbox cuộn -->
                <div class="h-48 overflow-y-auto rounded-xl border border-[var(--app-border)] bg-slate-50/40 dark:bg-slate-900/30 divide-y divide-[var(--app-border)]">
                    <template x-for="emp in filteredEmployees" :key="emp.id">
                        <label class="flex items-center gap-3 px-3 py-2.5 hover:bg-slate-100/80 dark:hover:bg-slate-800/60 cursor-pointer transition select-none">
                            <input type="checkbox"
                                   name="members[]"
                                   :value="emp.id"
                                   x-model="selectedMembers"
                                   class="rounded border-[var(--app-border)] text-indigo-600 focus:ring-indigo-500">
                            <div class="min-w-0 flex-1">
                                <p class="text-xs font-bold text-[var(--app-text)] truncate" x-text="emp.name"></p>
                                <p class="text-[11px] text-[var(--app-muted)] truncate" x-text="emp.position + ' • ' + emp.department_name"></p>
                            </div>
                        </label>
                    </template>
                    <div x-show="channelType === 'department' && !selectedDept" class="p-6 text-center text-xs text-[var(--app-muted)]">
                        Vui lòng chọn phòng ban trực thuộc ở trên để xem danh sách nhân sự của phòng.
                    </div>
                    <div x-show="(channelType !== 'department' || selectedDept) && filteredEmployees.length === 0" class="p-6 text-center text-xs text-[var(--app-muted)]">
                        Không tìm thấy nhân sự phù hợp.
                    </div>
                </div>
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
