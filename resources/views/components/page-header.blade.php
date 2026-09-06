@props(['title', 'description' => null, 'eyebrow' => null])
<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
    <div>
        @if ($eyebrow)
            <div class="mb-2 flex items-center gap-1.5">
                <span class="inline-flex items-center gap-1 rounded-md bg-indigo-500/10 dark:bg-indigo-500/20 px-2 py-0.5 text-[11px] font-bold uppercase tracking-widest text-indigo-600 dark:text-indigo-400">
                    {{ $eyebrow }}
                </span>
            </div>
        @endif
        <div class="flex items-center gap-3">
            <h1 class="text-2xl font-extrabold tracking-tight text-[var(--app-text)] sm:text-3xl">{{ $title }}</h1>
        </div>
        @if ($description)
            <p class="mt-1.5 max-w-2xl text-sm font-medium text-[var(--app-muted)]">{{ $description }}</p>
        @endif
    </div>
    @if ($slot->isNotEmpty())
        <div class="flex shrink-0 items-center gap-2.5">{{ $slot }}</div>
    @endif
</div>
