@extends('layouts.app')
@section('title', 'Thông báo của tôi')
@section('content')
<x-page-header eyebrow="Hệ thống" title="Thông báo của tôi" description="Xem và quản lý các thông báo trong hệ thống.">
    @if($unreadCount > 0)
        <form method="POST" action="{{ route('notifications.mark-all-read') }}">
            @csrf
            <button type="submit" class="app-button-secondary text-xs">
                Đánh dấu tất cả đã đọc
            </button>
        </form>
    @endif
</x-page-header>

<div class="mb-5 flex flex-wrap items-center justify-between gap-3">
    <!-- Filter Tabs -->
    <div class="flex gap-2">
        <a href="{{ route('notifications.index', ['filter' => 'all']) }}" class="rounded-xl px-3.5 py-1.5 text-xs font-semibold transition {{ $filter === 'all' ? 'bg-indigo-600 text-white shadow-xs' : 'border border-[var(--app-border)] bg-[var(--app-surface)] text-[var(--app-muted)] hover:text-[var(--app-text)]' }}">
            Tất cả ({{ auth()->user()->notifications()->count() }})
        </a>
        <a href="{{ route('notifications.index', ['filter' => 'unread']) }}" class="rounded-xl px-3.5 py-1.5 text-xs font-semibold transition {{ $filter === 'unread' ? 'bg-indigo-600 text-white shadow-xs' : 'border border-[var(--app-border)] bg-[var(--app-surface)] text-[var(--app-muted)] hover:text-[var(--app-text)]' }}">
            Chưa đọc ({{ $unreadCount }})
        </a>
    </div>
</div>

<div class="app-panel overflow-hidden">
    <div class="divide-y divide-[var(--app-border)]">
        @forelse($notifications as $notification)
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 p-4 transition hover:bg-slate-50 dark:hover:bg-slate-900/40 {{ $notification->read_at ? 'opacity-80' : 'bg-indigo-50/25 dark:bg-indigo-950/20' }}">
                <div class="flex items-start gap-3.5 min-w-0">
                    <div class="mt-0.5 shrink-0 rounded-xl p-2.5 {{ $notification->read_at ? 'bg-slate-100 text-slate-500 dark:bg-slate-800' : 'bg-indigo-500/10 text-indigo-600 dark:text-indigo-400' }}">
                        @if(($notification->data['icon'] ?? '') === 'calendar-clock')
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        @elseif(($notification->data['icon'] ?? '') === 'check-circle')
                            <svg class="h-5 w-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        @elseif(($notification->data['icon'] ?? '') === 'x-circle')
                            <svg class="h-5 w-5 text-rose-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        @else
                            <svg class="h-5 w-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 8.25h.01" /></svg>
                        @endif
                    </div>
                    <div class="min-w-0">
                        <div class="flex items-center gap-2">
                            <h3 class="text-sm font-bold text-[var(--app-text)]">{{ $notification->data['title'] ?? 'Thông báo' }}</h3>
                            @if(! $notification->read_at)
                                <span class="rounded-full bg-indigo-500/10 px-2 py-0.5 text-[10px] font-bold text-indigo-600 dark:text-indigo-400">Mới</span>
                            @endif
                        </div>
                        <p class="mt-1 text-xs text-[var(--app-muted)] leading-relaxed">{{ $notification->data['message'] ?? '' }}</p>
                        <span class="mt-1.5 block text-[11px] text-[var(--app-muted)] font-medium">{{ $notification->created_at->format('d/m/Y H:i') }} ({{ $notification->created_at->diffForHumans() }})</span>
                    </div>
                </div>

                <div class="flex items-center gap-2 shrink-0 self-end sm:self-center">
                    @if(!empty($notification->data['url']))
                        <form method="POST" action="{{ route('notifications.read', $notification->id) }}" class="inline">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="redirect_to" value="{{ $notification->data['url'] }}">
                            <button type="submit" class="rounded-xl border border-[var(--app-border)] bg-[var(--app-surface)] px-3 py-1.5 text-xs font-semibold text-indigo-600 hover:bg-slate-50 dark:hover:bg-slate-800">
                                Xem chi tiết
                            </button>
                        </form>
                    @endif

                    @if(! $notification->read_at)
                        <form method="POST" action="{{ route('notifications.read', $notification->id) }}" class="inline">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="rounded-xl border border-[var(--app-border)] bg-[var(--app-surface)] p-2 text-[var(--app-muted)] hover:text-[var(--app-text)] hover:bg-slate-50 dark:hover:bg-slate-800" title="Đánh dấu đã đọc">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                </svg>
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        @empty
            <div class="py-16 text-center text-sm text-[var(--app-muted)]">
                {{ $filter === 'unread' ? 'Không có thông báo chưa đọc nào.' : 'Bạn chưa có thông báo nào.' }}
            </div>
        @endforelse
    </div>

    @if($notifications->hasPages())
        <div class="border-t border-[var(--app-border)] p-4">
            {{ $notifications->links() }}
        </div>
    @endif
</div>
@endsection
