@props(['title', 'description' => null, 'eyebrow' => null])
<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
    <div>
        @if ($eyebrow)<p class="mb-2 text-xs font-semibold uppercase tracking-[0.2em] text-indigo-600">{{ $eyebrow }}</p>@endif
        <div class="flex items-center gap-3"><span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-orange-100 text-orange-700" aria-hidden="true"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="4" y="5" width="16" height="14" rx="2"/><path stroke-linecap="round" d="M8 9h8M8 13h5M8 17h3"/></svg></span><h1 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">{{ $title }}</h1></div>
        @if ($description)<p class="mt-2 max-w-2xl text-sm text-slate-500">{{ $description }}</p>@endif
    </div>
    @if ($slot->isNotEmpty())<div class="shrink-0">{{ $slot }}</div>@endif
</div>
