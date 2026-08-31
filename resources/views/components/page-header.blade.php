@props(['title', 'description' => null, 'eyebrow' => null])
<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
    <div>
        @if ($eyebrow)<p class="mb-2 text-xs font-semibold uppercase tracking-[0.2em] text-indigo-600">{{ $eyebrow }}</p>@endif
        <h1 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">{{ $title }}</h1>
        @if ($description)<p class="mt-2 max-w-2xl text-sm text-slate-500">{{ $description }}</p>@endif
    </div>
    @if ($slot->isNotEmpty())<div class="shrink-0">{{ $slot }}</div>@endif
</div>
