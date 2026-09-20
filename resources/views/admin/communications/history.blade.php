@extends('layouts.app')

@section('title', 'Lịch sử thư')

@section('content')
<x-page-header title="Lịch sử thư" />
<div class="mx-auto max-w-3xl">
    <a href="{{ route('admin.communications.index', ['section' => 'mail']) }}" class="app-button-secondary">Quay lại soạn thư</a>
    <section id="mail-history" class="mt-6" aria-labelledby="mail-history-title">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
            <h2 id="mail-history-title" class="text-lg font-bold text-[var(--app-text)]">Lịch sử thư đã gửi</h2>
            <span class="text-sm text-[var(--app-muted)]">{{ $messages->total() }} thư</span>
        </div>
        <div class="grid gap-4 sm:grid-cols-2">
            @forelse($messages as $message)
                <article class="flex min-w-0 flex-col overflow-hidden rounded-2xl border border-blue-200 bg-[var(--app-surface)] shadow-sm dark:border-blue-800">
                    <div class="flex items-center justify-between gap-3 border-b border-dashed border-blue-200 bg-blue-50 px-5 py-4 dark:border-blue-800 dark:bg-blue-950/40">
                        <svg class="h-7 w-7 shrink-0 text-blue-600 dark:text-blue-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg>
                        <time class="text-xs text-[var(--app-muted)]" datetime="{{ $message->sent_at?->toIso8601String() }}">{{ $message->sent_at?->format('d/m/Y · H:i') }}</time>
                    </div>
                    <div class="flex flex-1 flex-col p-5">
                        <h3 class="break-words font-bold text-[var(--app-text)]">{{ $message->subject }}</h3>
                        <dl class="mt-3 space-y-1 text-xs text-[var(--app-muted)]">
                            <div><dt class="inline">Từ: </dt><dd class="inline break-words">{{ $message->admin?->name ?? 'Quản trị viên' }}</dd></div>
                            <div><dt class="inline">Đến: </dt><dd class="inline">{{ match($message->audience) { 'all' => 'Nhân sự và nhân viên', 'hr' => 'Nhân sự', 'employee' => 'Nhân viên', default => 'Các tài khoản đã chọn' } }} · {{ $message->recipient_count }} người nhận</dd></div>
                        </dl>
                        <p class="my-4 break-words text-sm leading-relaxed text-[var(--app-muted)]">{{ \Illuminate\Support\Str::limit($message->body, 140) }}</p>
                        <details class="mt-auto border-t border-[var(--app-border)] pt-3">
                            <summary class="cursor-pointer text-sm font-semibold text-blue-600 focus-visible:outline focus-visible:outline-2 dark:text-blue-300">Đọc nội dung thư</summary>
                            <p class="mt-3 whitespace-pre-wrap break-words text-sm leading-relaxed text-[var(--app-text)]">{{ $message->body }}</p>
                        </details>
                    </div>
                </article>
            @empty
                <div class="sm:col-span-2"><x-empty-state title="Chưa có thư đã gửi" description="Thư gửi thành công sẽ xuất hiện tại đây." /></div>
            @endforelse
        </div>
        <div class="mt-5">{{ $messages->links() }}</div>
    </section>

</div>
@endsection
