@if (session('success') || session('error'))
    <div class="pointer-events-none fixed inset-x-4 top-4 z-[110] flex flex-col items-center gap-2 sm:inset-x-0">
        @foreach (['error', 'success'] as $type)
            @if (session($type))
                <div x-data="{ visible: true }" @if ($type === 'success') x-init="setTimeout(() => visible = false, 5000)" @endif
                    x-show="visible" x-transition:enter="transition duration-200 ease-out" x-transition:enter-start="-translate-y-2 scale-95 opacity-0" x-transition:enter-end="translate-y-0 scale-100 opacity-100"
                    class="glass-island-panel pointer-events-auto flex w-full max-w-lg items-center gap-3 rounded-[1.5rem] px-4 py-3 text-sm text-[var(--app-text)] sm:w-auto sm:min-w-72" role="{{ $type === 'error' ? 'alert' : 'status' }}">
                    <span class="h-2 w-2 shrink-0 rounded-full {{ $type === 'error' ? 'bg-rose-600' : 'bg-indigo-600' }}" aria-hidden="true"></span>
                    <span class="flex-1 font-medium">{{ session($type) }}</span>
                    <button type="button" @click="visible = false" class="rounded-full p-1 text-[var(--app-muted)] hover:bg-indigo-500/10 hover:text-[var(--app-text)]" aria-label="Đóng thông báo">&times;</button>
                </div>
            @endif
        @endforeach
    </div>
@endif
