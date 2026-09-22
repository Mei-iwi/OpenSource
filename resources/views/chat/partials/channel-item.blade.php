@props(['channel', 'active' => false])

<a href="{{ route('chat.channels.show', $channel->slug) }}"
   x-show="!searchQuery || '{{ strtolower($channel->slug . ' ' . $channel->name) }}'.includes(searchQuery.toLowerCase())"
   class="group flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-medium transition-all {{ $active
        ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/20 font-bold'
        : 'text-[var(--app-text)] hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
    
    <div class="flex items-center gap-2.5 min-w-0">
        <span class="text-xs {{ $active ? 'text-white' : ($channel->isCompany() ? 'text-amber-500' : ($channel->type === 'department' ? 'text-indigo-500' : 'text-emerald-500')) }}">
            {{ $channel->isCompany() ? '🌐' : '#' }}
        </span>
        <div class="truncate min-w-0">
            <span class="truncate block font-medium {{ $active ? 'text-white font-bold' : 'text-[var(--app-text)]' }}">
                #{{ $channel->slug }}
            </span>
            <span class="text-[10px] block truncate {{ $active ? 'text-indigo-100' : 'text-[var(--app-muted)]' }}">
                {{ $channel->name }}
            </span>
        </div>
    </div>

    @if(($channel->unread_count ?? 0) > 0)
        <span class="ml-2 flex-shrink-0 rounded-full px-2 py-0.5 text-[10px] font-extrabold shadow-sm {{ $active ? 'bg-white text-indigo-700' : 'bg-rose-500 text-white animate-pulse' }}">
            {{ $channel->unread_count }}
        </span>
    @endif
</a>
