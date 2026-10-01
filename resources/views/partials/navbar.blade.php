@php
    $roleLabels = ['admin' => 'Quản trị viên', 'hr' => 'Nhân sự', 'employee' => 'Nhân viên'];
    $pageTitle = trim($__env->yieldContent('title')) ?: 'Tổng quan';
    $profileRoute = 'profile.edit';
@endphp

<header class="sticky top-0 z-40 shrink-0 border-b border-[var(--app-border)] bg-[var(--app-surface)]/80 backdrop-blur-xl transition-colors">
    <div class="flex h-16 items-center justify-between px-4 sm:px-6 lg:px-8">
        <!-- Left Side: Mobile Menu Button & Breadcrumb/Module Title -->
        <div class="flex items-center gap-3">
            <button type="button" @click="mobileMenuOpen = !mobileMenuOpen" :aria-expanded="mobileMenuOpen" class="rounded-xl p-2 text-[var(--app-muted)] transition hover:bg-slate-100 hover:text-[var(--app-text)] focus:outline-none dark:hover:bg-slate-800 lg:hidden" aria-label="Mở menu">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>

            <!-- Module Icon & Title -->
            <div class="flex items-center gap-3">
                {{-- Fixed brand artwork; independent of editable user avatars. --}}
                <img src="{{ asset('images/ctq-office-cat.png') }}" alt="Mèo công sở CTQ ngơ ngác ôm cà phê" width="40" height="40" draggable="false" class="hidden h-10 w-10 shrink-0 rounded-xl border border-[var(--app-border)] object-cover shadow-sm sm:block">
                <h1 class="text-sm font-bold tracking-tight text-[var(--app-text)] sm:text-base">{{ $pageTitle }}</h1>
            </div>
        </div>

        <!-- Center: Live Clock & System Status Widget (Hidden on mobile) -->
        <div x-data="{
            time: '',
            date: '',
            init() {
                this.updateClock();
                setInterval(() => this.updateClock(), 1000);
            },
            updateClock() {
                const now = new Date();
                this.time = now.toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
                this.date = now.toLocaleDateString('vi-VN', { weekday: 'short', day: '2-digit', month: '2-digit' });
            }
        }" class="hidden items-center gap-3 rounded-full border border-[var(--app-border)] bg-[var(--app-surface)]/60 px-4 py-1.5 shadow-xs md:flex">
            <span class="relative flex h-2 w-2">
                <span class="absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-40"></span>
                <span class="relative inline-flex h-2 w-2 rounded-full bg-emerald-500"></span>
            </span>
            <span class="text-xs font-semibold text-[var(--app-text)] font-mono" x-text="time">--:--:--</span>
            <span class="text-xs text-[var(--app-muted)] font-medium" x-text="date">--/--</span>
        </div>

        <!-- Right Side: Controls & User Profile -->
        <div class="relative flex items-center gap-2 sm:gap-3" x-data="{ userMenuOpen: false, logoutConfirm: false }">
            <!-- Theme Switcher Button -->
            <button type="button" @click="toggleTheme()" class="flex h-9 w-9 items-center justify-center rounded-xl border border-[var(--app-border)] bg-[var(--app-surface)] text-[var(--app-muted)] transition hover:border-indigo-300 hover:text-indigo-600 dark:hover:border-indigo-700 dark:hover:text-indigo-400" :title="dark ? 'Chuyển sang nền sáng' : 'Chuyển sang nền tối'" aria-label="Chuyển đổi nền sáng tối">
                <svg x-show="!dark" class="h-4 w-4 text-amber-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="4"/><path d="M12 2v2"/><path d="M12 20v2"/><path d="m4.93 4.93 1.41 1.41"/><path d="m17.66 17.66 1.41 1.41"/><path d="M2 12h2"/><path d="M20 12h2"/><path d="m6.34 17.66-1.41 1.41"/><path d="m19.07 4.93-1.41 1.41"/>
                </svg>
                <svg x-show="dark" x-cloak class="h-4 w-4 text-indigo-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/>
                </svg>
            </button>

            <!-- Notifications Bell Dropdown -->
            @php
                $unreadNotificationsCount = auth()->user()->unreadNotifications()->count();
                $recentNotifications = auth()->user()->notifications()->take(6)->get();
            @endphp
            <div class="relative" x-data="{ notificationsOpen: false }">
                <button type="button" @click="notificationsOpen = !notificationsOpen" class="relative flex h-9 w-9 items-center justify-center rounded-xl border border-[var(--app-border)] bg-[var(--app-surface)] text-[var(--app-muted)] transition hover:border-indigo-300 hover:text-indigo-600 dark:hover:border-indigo-700 dark:hover:text-indigo-400" title="Thông báo" aria-label="Xem thông báo">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                    </svg>
                    @if($unreadNotificationsCount > 0)
                        <span class="absolute -top-1 -right-1 flex h-4 min-w-4 items-center justify-center rounded-full bg-rose-600 px-1 text-[10px] font-bold text-white shadow-xs">
                            {{ $unreadNotificationsCount > 99 ? '99+' : $unreadNotificationsCount }}
                        </span>
                    @endif
                </button>

                <!-- Notifications Dropdown -->
                <div x-show="notificationsOpen" x-cloak @click.outside="notificationsOpen = false" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" class="absolute right-0 top-full mt-2.5 z-50 w-80 sm:w-96 rounded-2xl border border-[var(--app-border)] bg-[var(--app-surface)] shadow-2xl overflow-hidden" role="menu">
                    <!-- Dropdown Header -->
                    <div class="flex items-center justify-between border-b border-[var(--app-border)] px-4 py-3 bg-[var(--app-bg)]/40">
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-bold text-[var(--app-text)]">Thông báo</span>
                            @if($unreadNotificationsCount > 0)
                                <span class="rounded-full bg-rose-500/10 px-2 py-0.5 text-xs font-semibold text-rose-600 dark:text-rose-400">
                                    {{ $unreadNotificationsCount }} mới
                                </span>
                            @endif
                        </div>
                        @if($unreadNotificationsCount > 0)
                            <form method="POST" action="{{ route('notifications.mark-all-read') }}">
                                @csrf
                                <button type="submit" class="text-xs font-semibold text-indigo-600 hover:text-indigo-700 dark:text-indigo-400">Đã đọc tất cả</button>
                            </form>
                        @endif
                    </div>

                    <!-- Dropdown Content / List -->
                    <div class="max-h-[22rem] divide-y divide-[var(--app-border)] overflow-y-auto">
                        @forelse($recentNotifications as $notification)
                            <div class="group relative flex items-start gap-3 p-3.5 transition hover:bg-slate-50 dark:hover:bg-slate-900/40 {{ $notification->read_at ? 'opacity-70' : 'bg-indigo-50/20 dark:bg-indigo-950/15' }}">
                                <div class="mt-0.5 shrink-0 rounded-lg p-2 {{ $notification->read_at ? 'bg-slate-100 text-slate-500 dark:bg-slate-800' : 'bg-indigo-500/10 text-indigo-600 dark:text-indigo-400' }}">
                                    @if(($notification->data['icon'] ?? '') === 'calendar-clock')
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    @elseif(($notification->data['icon'] ?? '') === 'check-circle')
                                        <svg class="h-4 w-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    @elseif(($notification->data['icon'] ?? '') === 'x-circle')
                                        <svg class="h-4 w-4 text-rose-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    @else
                                        <svg class="h-4 w-4 text-amber-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 8.25h.01" /></svg>
                                    @endif
                                </div>
                                <div class="min-w-0 flex-1">
                                    <form method="POST" action="{{ route('notifications.read', $notification->id) }}" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="redirect_to" value="{{ $notification->data['url'] ?? '' }}">
                                        <button type="submit" class="w-full text-left font-semibold text-xs text-[var(--app-text)] hover:text-indigo-600 focus:outline-none">
                                            {{ $notification->data['title'] ?? 'Thông báo' }}
                                        </button>
                                    </form>
                                    <p class="mt-0.5 text-xs text-[var(--app-muted)] line-clamp-2 leading-relaxed">{{ $notification->data['message'] ?? '' }}</p>
                                    <span class="mt-1 block text-[10px] text-[var(--app-muted)] font-medium">{{ $notification->created_at->diffForHumans() }}</span>
                                </div>
                                @if(! $notification->read_at)
                                    <span class="mt-2 h-2 w-2 shrink-0 rounded-full bg-indigo-600" title="Chưa đọc"></span>
                                @endif
                            </div>
                        @empty
                            <div class="py-8 text-center text-xs text-[var(--app-muted)]">
                                Bạn chưa có thông báo nào.
                            </div>
                        @endforelse
                    </div>

                    <!-- Dropdown Footer -->
                    <div class="border-t border-[var(--app-border)] p-2 text-center bg-[var(--app-bg)]/40">
                        <a href="{{ route('notifications.index') }}" class="block w-full py-1.5 text-xs font-semibold text-indigo-600 hover:text-indigo-700 dark:text-indigo-400">
                            Xem tất cả thông báo
                        </a>
                    </div>
                </div>
            </div>

            <!-- User Profile Menu Wrapper -->
            <div class="relative" x-data="{ userMenuOpen: false }">
                <!-- User Menu Trigger -->
                <button type="button" @click="userMenuOpen = !userMenuOpen" :aria-expanded="userMenuOpen" class="user-profile-trigger flex items-center gap-2.5 border-0 bg-transparent p-0 shadow-none transition hover:opacity-80 focus:outline-none focus:ring-2 focus:ring-indigo-500" aria-label="Mở menu tài khoản">
                    <x-user-avatar :user="auth()->user()" class="snake-avatar-motion h-8 w-8 rounded-xl bg-gradient-to-tr from-indigo-600 to-sky-500 shadow-sm" />
                    <div class="hidden text-left sm:block">
                        <p class="max-w-28 truncate text-xs font-bold text-[var(--app-text)] sm:max-w-36">{{ auth()->user()->name }}</p>
                        <p class="text-[10px] font-medium text-[var(--app-muted)]">{{ $roleLabels[auth()->user()->role] ?? auth()->user()->role }}</p>
                    </div>
                    <svg class="h-3.5 w-3.5 text-[var(--app-muted)]" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.51a.75.75 0 01-1.08 0l-4.25-4.51a.75.75 0 01.02-1.06z" clip-rule="evenodd"/>
                    </svg>
                </button>

                <!-- Dropdown Menu -->
                <div x-show="userMenuOpen" x-cloak @click.outside="userMenuOpen = false" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" class="absolute right-0 top-full mt-2.5 z-50 w-72 rounded-2xl border border-[var(--app-border)] bg-[var(--app-surface)] p-2 shadow-2xl" role="menu">
                    <!-- User Info Header -->
                    <div class="border-b border-[var(--app-border)] px-3 py-2.5">
                        <p class="truncate text-sm font-bold text-[var(--app-text)]">{{ auth()->user()->name }}</p>
                        <p class="truncate text-xs text-[var(--app-muted)]">{{ auth()->user()->email }}</p>
                        <span class="mt-1.5 inline-block rounded-md bg-indigo-500/10 px-2 py-0.5 text-[10px] font-semibold text-indigo-600 dark:text-indigo-400">
                            {{ $roleLabels[auth()->user()->role] ?? auth()->user()->role }}
                        </span>
                    </div>

                    <div class="py-1">
                        <a href="{{ route($profileRoute) }}" class="flex items-center gap-2.5 rounded-xl px-3 py-2 text-xs font-semibold text-[var(--app-text)] transition hover:bg-indigo-50 hover:text-indigo-600 dark:hover:bg-indigo-950/40 dark:hover:text-indigo-400" role="menuitem">
                            <svg class="h-4 w-4 text-[var(--app-muted)]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="5"/><path d="M20 21a8 8 0 1 0-16 0"/></svg>
                            Hồ sơ cá nhân
                        </a>
                    </div>

                    <!-- Interface Customizer Section -->
                    <div class="my-1 border-t border-[var(--app-border)] pt-2">
                        <p class="px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-[var(--app-muted)]">Tùy biến giao diện</p>


                        <div class="px-3 pb-2 pt-1">
                            <label class="block text-[10px] font-semibold text-[var(--app-muted)]" for="nav-position">Vị trí menu</label>
                            <select id="nav-position" x-model="navPosition" @change="setNavPosition($event.target.value)" class="mt-1 w-full rounded-lg border border-[var(--app-border)] bg-[var(--app-surface)] px-2 py-1.5 text-xs text-[var(--app-text)]">
                                <option value="left">Bên trái (chuẩn)</option>
                                <option value="right">Bên phải</option>
                                <option value="top">Phía trên</option>
                                <option value="bottom">Phía dưới</option>
                            </select>
                        </div>
                    </div>

                    <!-- Logout Button -->
                    <div class="border-t border-[var(--app-border)] pt-1">
                        <button type="button" @click="$dispatch('open-logout'); userMenuOpen = false" class="flex w-full items-center gap-2 rounded-xl px-3 py-2 text-left text-xs font-semibold text-rose-600 transition hover:bg-rose-50 dark:hover:bg-rose-950/30" role="menuitem">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" x2="9" y1="12" y2="12"/></svg>
                            Đăng xuất
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>
