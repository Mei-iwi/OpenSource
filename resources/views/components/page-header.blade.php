@props(['title', 'description' => null, 'eyebrow' => null])
<div class="mb-6 text-center">
    <div class="flex flex-col items-center">
        @if ($eyebrow)
            <div class="mb-2 flex items-center justify-center gap-1.5">
                <span class="inline-flex items-center gap-1 rounded-md bg-indigo-500/10 dark:bg-indigo-500/20 px-2 py-0.5 text-[11px] font-bold uppercase tracking-widest text-indigo-600 dark:text-indigo-400">
                    {{ $eyebrow }}
                </span>
            </div>
        @endif
        <div class="flex items-center justify-center gap-3">
            <h1 class="text-2xl font-extrabold tracking-tight text-[var(--app-text)] sm:text-3xl">{{ $title }}</h1>
        </div>
    </div>
    @if ($slot->isNotEmpty())
        <div class="mt-4 flex flex-wrap items-center justify-center gap-2.5">{{ $slot }}</div>
    @endif
</div>
