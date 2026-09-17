@extends('layouts.app')

@section('title', 'Hộp thư')

@section('content')
<x-page-header eyebrow="Truyền thông nội bộ" title="Hộp thư" description="Các thông báo và thư Admin gửi đến tài khoản của bạn." />

<div class="space-y-4">
    @forelse($messages as $message)
        <article class="app-panel p-5 sm:p-6">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-[0.16em] text-indigo-600 dark:text-indigo-400">Thư từ Admin</p>
                    <h2 class="mt-1 text-lg font-extrabold text-[var(--app-text)]">{{ $message->subject }}</h2>
                </div>
                <time class="text-xs text-[var(--app-muted)]">{{ $message->sent_at?->format('d/m/Y H:i') }}</time>
            </div>
            <div class="mt-4 whitespace-pre-line text-sm leading-relaxed text-[var(--app-text)]">{{ $message->body }}</div>
            <p class="mt-5 border-t border-[var(--app-border)] pt-3 text-xs text-[var(--app-muted)]">Gửi bởi {{ $message->admin?->name ?? 'Quản trị viên' }}</p>
        </article>
    @empty
        <div class="app-panel p-8">
            <x-empty-state title="Chưa có thư" description="Khi Admin gửi thông báo, thư sẽ xuất hiện tại đây." />
        </div>
    @endforelse
</div>

<div class="mt-6">{{ $messages->links() }}</div>
@endsection
