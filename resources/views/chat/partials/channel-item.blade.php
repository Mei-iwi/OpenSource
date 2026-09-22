@props(['channel', 'active' => false])

@php
    $isDirect = $channel->isDirect();
    $partner = $isDirect ? $channel->getDirectPartner(auth()->user()) : null;
    $displayName = $isDirect ? ($partner?->name ?? 'Đồng nghiệp') : ('#' . $channel->slug);
    $subName = $isDirect ? ($partner?->employee?->department?->name ?? $partner?->role ?? '') : $channel->name;
    $searchKey = strtolower($displayName . ' ' . $subName . ' ' . $channel->slug);
@endphp

<a href="{{ route('chat.channels.show', $channel->slug) }}"
   x-show="!searchQuery || '{{ addslashes($searchKey) }}'.includes(searchQuery.toLowerCase())"
   class="group flex items-center justify-between px-3 py-2 rounded-xl text-xs font-medium transition-all {{ $active
        ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/20 font-bold'
        : 'text-[var(--app-text)] hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
    
    <div class="flex items-center gap-2.5 min-w-0">
        @if($isDirect)
            <div class="relative h-7 w-7 rounded-lg overflow-hidden shrink-0 flex items-center justify-center font-bold text-[11px] {{ $active ? 'bg-white/20 text-white' : 'bg-slate-200 dark:bg-slate-700 text-[var(--app-text)]' }}">
                @if($partner?->avatar_url)
                    <img src="{{ $partner->avatar_url }}" alt="" class="h-full w-full object-cover">
                @else
                    {{ substr($partner?->name ?? 'U', 0, 1) }}
                @endif
            </div>
        @else
            <span class="text-xs font-mono font-bold {{ $active ? 'text-white' : ($channel->isCompany() ? 'text-amber-500' : ($channel->type === 'department' ? 'text-indigo-500' : 'text-emerald-500')) }}">
                {{ $channel->isCompany() ? '🏢' : '#' }}
            </span>
        @endif

        <div class="truncate min-w-0">
            <span class="truncate block font-medium {{ $active ? 'text-white font-bold' : 'text-[var(--app-text)]' }}">
                {{ $displayName }}
            </span>
            @if($subName && ! $isDirect)
                <span class="text-[10px] block truncate {{ $active ? 'text-indigo-100' : 'text-[var(--app-muted)]' }}">
                    {{ $subName }}
                </span>
            @elseif($isDirect && $subName)
                <span class="text-[10px] block truncate {{ $active ? 'text-indigo-100' : 'text-[var(--app-muted)]' }}">
                    {{ $subName }}
                </span>
            @endif
        </div>
    </div>

    @if(($channel->unread_count ?? 0) > 0)
        <span class="ml-2 flex-shrink-0 rounded-full px-2 py-0.5 text-[10px] font-extrabold shadow-sm {{ $active ? 'bg-white text-indigo-700' : 'bg-rose-500 text-white animate-pulse' }}">
            {{ $channel->unread_count }}
        </span>
    @endif
</a>
