@extends('layouts.app')

@section('title', 'Thành viên #' . $channel->slug . ' — Chat nội bộ')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('chat.channels.show', $channel->slug) }}" class="p-2 rounded-xl border border-[var(--app-border)] text-[var(--app-muted)] hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m15 18-6-6 6-6"/></svg>
            </a>
            <div>
                <h1 class="text-xl font-bold text-[var(--app-text)]">Thành viên #{{ $channel->slug }}</h1>
                <p class="text-xs text-[var(--app-muted)]">{{ $channel->name }} ({{ $members->count() }} thành viên)</p>
            </div>
        </div>
    </div>

    <!-- Main Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Left 2 Cols: Members List -->
        <div class="lg:col-span-2 space-y-4">
            <div class="rounded-2xl border border-[var(--app-border)] bg-[var(--app-surface)] shadow-sm overflow-hidden">
                <div class="p-4 border-b border-[var(--app-border)] bg-slate-50/50 dark:bg-slate-800/30">
                    <h2 class="text-xs font-bold text-[var(--app-text)] uppercase tracking-wider">Danh sách thành viên hiện tại</h2>
                </div>
                <div class="divide-y divide-[var(--app-border)]">
                    @forelse($members as $member)
                        <div class="p-4 flex items-center justify-between hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="h-10 w-10 rounded-xl overflow-hidden bg-slate-200 dark:bg-slate-700 flex items-center justify-center font-bold text-sm text-[var(--app-text)] shrink-0">
                                    @if($member->user?->avatar_url)
                                        <img src="{{ $member->user->avatar_url }}" alt="" class="h-full w-full object-cover">
                                    @else
                                        {{ substr($member->user?->name ?? 'U', 0, 1) }}
                                    @endif
                                </div>
                                <div class="min-w-0">
                                    <p class="text-sm font-bold text-[var(--app-text)] truncate">{{ $member->user?->name }}</p>
                                    <p class="text-xs text-[var(--app-muted)] truncate">{{ $member->user?->email }}</p>
                                    <div class="flex items-center gap-2 mt-1">
                                        <span class="rounded bg-slate-200/70 dark:bg-slate-700/70 px-1.5 py-0.5 text-[10px] font-semibold text-[var(--app-text)]">
                                            {{ $member->user?->employee?->department?->name ?? 'Không phòng ban' }}
                                        </span>
                                        <span class="rounded bg-indigo-500/15 px-1.5 py-0.5 text-[10px] font-bold text-indigo-600 dark:text-indigo-400">
                                            {{ $member->user?->role }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            @if(auth()->user()->can('manageMembers', $channel) && $member->user_id !== auth()->id())
                                <form action="{{ route('chat.channels.members.destroy', ['channel' => $channel->slug, 'user' => $member->user_id]) }}" method="POST" onsubmit="return confirm('Xóa thành viên này khỏi kênh?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-3 py-1.5 rounded-xl border border-rose-300 dark:border-rose-900 text-xs font-semibold text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition">
                                        Xóa khỏi kênh
                                    </button>
                                </form>
                            @endif
                        </div>
                    @empty
                        <div class="p-8 text-center text-xs text-[var(--app-muted)]">Chưa có thành viên nào trong kênh.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Right Col: Add Members (Admin/HR only) -->
        <div class="space-y-4">
            @if(auth()->user()->can('manageMembers', $channel))
                <div class="rounded-2xl border border-[var(--app-border)] bg-[var(--app-surface)] p-6 shadow-sm space-y-4">
                    <h2 class="text-sm font-bold text-[var(--app-text)]">Thêm nhân sự vào kênh</h2>
                    <form action="{{ route('chat.channels.members.store', $channel->slug) }}" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-xs font-medium text-[var(--app-text)] mb-1">Chọn nhân sự</label>
                            <select name="user_ids[]" multiple class="w-full h-64 rounded-xl border border-[var(--app-border)] bg-[var(--app-surface)] px-3 py-2 text-xs text-[var(--app-text)] focus:ring-2 focus:ring-indigo-500/40">
                                @forelse($availableUsers as $user)
                                    <option value="{{ $user->id }}">
                                        {{ $user->name }} ({{ $user->employee?->department?->name ?? 'Không phòng' }} — {{ $user->role }})
                                    </option>
                                @empty
                                    <option disabled>Tất cả nhân sự đã thuộc kênh</option>
                                @endforelse
                            </select>
                            <p class="mt-1 text-[11px] text-[var(--app-muted)]">Giữ phím Ctrl hoặc Cmd để chọn nhiều nhân sự.</p>
                        </div>

                        <button type="submit" class="w-full py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-md shadow-indigo-600/25 transition">
                            Thêm nhân sự được chọn
                        </button>
                    </form>
                </div>
            @else
                <div class="rounded-2xl border border-[var(--app-border)] bg-slate-50/50 dark:bg-slate-900/20 p-5 text-center text-xs text-[var(--app-muted)]">
                    Bạn không có quyền quản lý thêm hoặc xóa thành viên trong kênh này.
                </div>
            @endif
        </div>

    </div>

</div>
@endsection
