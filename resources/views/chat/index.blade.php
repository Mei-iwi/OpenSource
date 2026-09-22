@extends('layouts.app')

@section('title', $currentChannel ? ($currentChannel->isDirect() ? ($currentChannel->getDirectPartner(auth()->user())?->name ?? 'Tin nhắn riêng') : '#' . $currentChannel->slug) . ' — Chat nội bộ' : 'Chat nội bộ')

@section('content')
    <div class="h-[calc(100vh-8.5rem)] flex flex-col md:flex-row rounded-2xl border border-[var(--app-border)] bg-[var(--app-surface)] shadow-xl overflow-hidden"
        x-data="chatComponent({
            channelId: {{ $currentChannel ? $currentChannel->id : 'null' }},
            channelSlug: '{{ $currentChannel ? $currentChannel->slug : '' }}',
            isCompany: {{ $currentChannel && $currentChannel->isCompany() ? 'true' : 'false' }},
            isDirect: {{ $currentChannel && $currentChannel->isDirect() ? 'true' : 'false' }},
            canSend: {{ $currentChannel && auth()->user()->can('sendMessage', $currentChannel) ? 'true' : 'false' }},
            initialMessages: @js(
                $messages->map(fn($m) => [
                    'id' => $m->id,
                    'user_id' => $m->user_id,
                    'sender_name' => $m->user?->name ?? 'Người dùng',
                    'sender_avatar' => $m->user?->avatar_url,
                    'sender_role' => $m->user?->role,
                    'sender_position' => $m->user?->employee?->position,
                    'sender_department' => $m->user?->employee?->department?->name,
                    'message' => $m->formatted_html,
                    'raw_message' => $m->message,
                    'attachments' => $m->attachments->map(fn ($att) => [
                        'id' => $att->id,
                        'file_name' => $att->file_name,
                        'file_size' => $att->formatted_size,
                        'mime_type' => $att->mime_type,
                        'is_image' => (bool)$att->is_image,
                        'url' => $att->url,
                    ]),
                    'reactions' => $m->reactions_summary,
                    'is_me' => $m->user_id === auth()->id(),
                    'can_edit' => auth()->user()->can('update', $m),
                    'can_delete' => auth()->user()->can('delete', $m),
                    'edited' => $m->isEdited(),
                    'created_at_human' => $m->created_at?->format('H:i') ?? '',
                    'created_at_full' => $m->created_at?->format('d/m/Y H:i') ?? '',
                ])->values()
            ),
            currentUserId: {{ auth()->id() }},
            csrfToken: '{{ csrf_token() }}',
            routes: {
                getMessages: '{{ $currentChannel ? route('chat.channels.messages.index', $currentChannel->slug) : '' }}',
                sendMessage: '{{ $currentChannel ? route('chat.channels.messages.store', $currentChannel->slug) : '' }}',
                markAsRead: '{{ $currentChannel ? route('chat.channels.read', $currentChannel->slug) : '' }}',
                unreadSummary: '{{ route('chat.unread-summary') }}',
            }
        })" x-init="initChat()">

        <!-- LEFT SIDEBAR: DANH SÁCH CHANNEL & ĐỒNG NGHIỆP -->
        <div class="w-full md:w-80 lg:w-96 flex-shrink-0 flex flex-col border-b md:border-b-0 md:border-r border-[var(--app-border)] bg-slate-50/50 dark:bg-slate-900/30">

            <!-- Header & Action Buttons -->
            <div class="p-4 border-b border-[var(--app-border)] space-y-3">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-400">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M7.9 20A9 9 0 1 0 4 16.1L2 22Z" />
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-base font-bold text-[var(--app-text)] leading-tight">Chat Nội Bộ</h1>
                            <p class="text-[11px] text-[var(--app-muted)]">Giao tiếp & Trao đổi công việc</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-1.5">
                        <!-- Nút Nhắn tin mới (Direct Message cho tất cả nhân sự) -->
                        <button type="button" @click="directModalOpen = true"
                            class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-xl border border-[var(--app-border)] hover:bg-slate-100 dark:hover:bg-slate-800 text-[var(--app-text)] text-xs font-semibold transition"
                            title="Nhắn tin riêng với đồng nghiệp">
                            <svg class="w-3.5 h-3.5 text-indigo-600 dark:text-indigo-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/></svg>
                            <span>Nhắn tin</span>
                        </button>

                        @if (auth()->user()->isAdmin() || auth()->user()->isHr())
                            <a href="{{ route('chat.channels.create') }}"
                                class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-sm transition"
                                title="Tạo kênh mới">
                                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                <span>Tạo kênh</span>
                            </a>
                        @endif
                    </div>
                </div>

                <!-- Ô tìm kiếm kênh hoặc đồng nghiệp -->
                <div class="relative">
                    <input type="text" x-model="searchQuery" placeholder="Tìm kênh, phòng ban, đồng nghiệp..."
                        class="w-full pl-9 pr-3 py-2 text-xs rounded-xl border border-[var(--app-border)] bg-[var(--app-surface)] text-[var(--app-text)] placeholder-[var(--app-muted)] focus:outline-none focus:ring-2 focus:ring-indigo-500/40">
                    <svg class="absolute left-3 top-2.5 h-4 w-4 text-[var(--app-muted)]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8" />
                        <line x1="21" y1="21" x2="16.65" y2="16.65" />
                    </svg>
                </div>
            </div>

            <!-- Channels Grouped List -->
            <div class="flex-1 overflow-y-auto p-3 space-y-4">

                <!-- Nhóm 1: Toàn công ty -->
                @php $companyChannels = $channels->filter(fn($c) => $c->isCompany()); @endphp
                @if ($companyChannels->isNotEmpty())
                    <div>
                        <p class="px-2.5 text-[10px] font-extrabold uppercase tracking-wider text-[var(--app-muted)] flex items-center justify-between">
                            <span>Toàn Công Ty</span>
                            <span class="text-[9px] font-normal">{{ $companyChannels->count() }}</span>
                        </p>
                        <div class="mt-1.5 space-y-1">
                            @foreach ($companyChannels as $ch)
                                @include('chat.partials.channel-item', [
                                    'channel' => $ch,
                                    'active' => $currentChannel && $currentChannel->id === $ch->id,
                                ])
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Nhóm 2: Phòng ban -->
                @php $deptChannels = $channels->filter(fn($c) => !$c->isCompany() && $c->type === 'department'); @endphp
                @if ($deptChannels->isNotEmpty())
                    <div>
                        <p class="px-2.5 text-[10px] font-extrabold uppercase tracking-wider text-[var(--app-muted)] flex items-center justify-between">
                            <span>Kênh Phòng Ban</span>
                            <span class="text-[9px] font-normal">{{ $deptChannels->count() }}</span>
                        </p>
                        <div class="mt-1.5 space-y-1">
                            @foreach ($deptChannels as $ch)
                                @include('chat.partials.channel-item', [
                                    'channel' => $ch,
                                    'active' => $currentChannel && $currentChannel->id === $ch->id,
                                ])
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Nhóm 3: Nhóm dự án -->
                @php $groupChannels = $channels->filter(fn($c) => !$c->isCompany() && $c->type === 'group'); @endphp
                @if ($groupChannels->isNotEmpty())
                    <div>
                        <p class="px-2.5 text-[10px] font-extrabold uppercase tracking-wider text-[var(--app-muted)] flex items-center justify-between">
                            <span>Nhóm & Dự Án</span>
                            <span class="text-[9px] font-normal">{{ $groupChannels->count() }}</span>
                        </p>
                        <div class="mt-1.5 space-y-1">
                            @foreach ($groupChannels as $ch)
                                @include('chat.partials.channel-item', [
                                    'channel' => $ch,
                                    'active' => $currentChannel && $currentChannel->id === $ch->id,
                                ])
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Nhóm 4: Tin nhắn trực tiếp 1-1 -->
                @php $directChannels = $channels->filter(fn($c) => $c->isDirect()); @endphp
                <div>
                    <div class="px-2.5 flex items-center justify-between text-[10px] font-extrabold uppercase tracking-wider text-[var(--app-muted)]">
                        <span>Tin Nhắn Trực Tiếp</span>
                        <button type="button" @click="directModalOpen = true" class="text-indigo-600 dark:text-indigo-400 hover:underline font-bold text-xs" title="Bắt đầu cuộc trò chuyện mới">+</button>
                    </div>
                    <div class="mt-1.5 space-y-1">
                        @forelse ($directChannels as $ch)
                            @include('chat.partials.channel-item', [
                                'channel' => $ch,
                                'active' => $currentChannel && $currentChannel->id === $ch->id,
                            ])
                        @empty
                            <div class="px-2.5 py-2 text-[11px] text-[var(--app-muted)] italic">
                                Chưa có tin nhắn riêng nào. Nhấn "+ " để trò chuyện cùng đồng nghiệp.
                            </div>
                        @endforelse
                    </div>
                </div>

            </div>
        </div>

        <!-- RIGHT AREA: VÙNG CHAT CHÍNH -->
        <div class="flex-1 flex flex-col min-w-0 bg-[var(--app-surface)] relative">
            @if ($currentChannel)
                @php
                    $isDirect = $currentChannel->isDirect();
                    $directPartner = $isDirect ? $currentChannel->getDirectPartner(auth()->user()) : null;
                @endphp

                <!-- Channel Header -->
                <div class="h-16 flex-shrink-0 px-4 sm:px-6 flex items-center justify-between border-b border-[var(--app-border)] bg-[var(--app-surface)] z-10">
                    <div class="flex items-center gap-3 min-w-0">
                        @if ($isDirect && $directPartner)
                            <button type="button" @click="openProfileCard({{ $directPartner->id }})" class="relative h-10 w-10 shrink-0 rounded-xl overflow-hidden bg-slate-200 dark:bg-slate-700 flex items-center justify-center font-bold text-sm text-[var(--app-text)] hover:ring-2 hover:ring-indigo-500 transition">
                                @if ($directPartner->avatar_url)
                                    <img src="{{ $directPartner->avatar_url }}" alt="" class="h-full w-full object-cover">
                                @else
                                    {{ substr($directPartner->name, 0, 1) }}
                                @endif
                            </button>
                            <div class="min-w-0">
                                <div class="flex items-center gap-2">
                                    <button type="button" @click="openProfileCard({{ $directPartner->id }})" class="text-sm sm:text-base font-bold text-[var(--app-text)] truncate hover:text-indigo-600 transition">
                                        {{ $directPartner->name }}
                                    </button>
                                    <span class="rounded-md bg-violet-500/15 px-2 py-0.5 text-[10px] font-bold text-violet-600 dark:text-violet-400">Trực tiếp</span>
                                </div>
                                <p class="text-xs text-[var(--app-muted)] truncate max-w-md">
                                    {{ $directPartner->employee?->position ?? 'Nhân viên' }} • {{ $directPartner->employee?->department?->name ?? 'Công ty' }}
                                </p>
                            </div>
                        @else
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl font-mono font-extrabold text-sm {{ $currentChannel->isCompany() ? 'bg-amber-500/10 text-amber-600 dark:text-amber-400' : ($currentChannel->type === 'department' ? 'bg-indigo-500/10 text-indigo-600 dark:text-indigo-400' : 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400') }}">
                                {{ $currentChannel->isCompany() ? '🏢' : '#' }}
                            </div>
                            <div class="min-w-0">
                                <div class="flex items-center gap-2">
                                    <h2 class="text-sm sm:text-base font-bold text-[var(--app-text)] truncate">
                                        #{{ $currentChannel->slug }}
                                    </h2>
                                    @if ($currentChannel->isCompany())
                                        <span class="rounded-md bg-amber-500/15 px-2 py-0.5 text-[10px] font-bold text-amber-600 dark:text-amber-400">Toàn công ty</span>
                                    @elseif($currentChannel->type === 'department')
                                        <span class="rounded-md bg-indigo-500/15 px-2 py-0.5 text-[10px] font-bold text-indigo-600 dark:text-indigo-400">Phòng ban: {{ $currentChannel->department?->name }}</span>
                                    @else
                                        <span class="rounded-md bg-emerald-500/15 px-2 py-0.5 text-[10px] font-bold text-emerald-600 dark:text-emerald-400">Nhóm</span>
                                    @endif
                                </div>
                                <p class="text-xs text-[var(--app-muted)] truncate max-w-md">
                                    {{ $currentChannel->description ?: 'Không có mô tả kênh.' }}
                                </p>
                            </div>
                        @endif
                    </div>

                    <!-- Channel Actions -->
                    <div class="flex items-center gap-2">
                        @if (! $isDirect)
                            <button type="button" @click="membersModalOpen = true"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-[var(--app-border)] text-xs font-semibold text-[var(--app-text)] hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                                <svg class="h-4 w-4 text-[var(--app-muted)]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                                    <circle cx="9" cy="7" r="4" />
                                    <path d="M22 21v-2a4 4 0 0 0-3-3.87" />
                                    <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                                </svg>
                                <span class="hidden sm:inline">Thành viên</span>
                                <span class="rounded-full bg-slate-200 dark:bg-slate-700 px-1.5 py-0.2 text-[10px] font-bold">{{ $currentChannel->members->count() }}</span>
                            </button>
                        @endif

                        @if (auth()->user()->can('update', $currentChannel))
                            <a href="{{ route('chat.channels.edit', $currentChannel->slug) }}"
                                class="inline-flex items-center p-2 rounded-xl border border-[var(--app-border)] text-[var(--app-muted)] hover:text-indigo-600 hover:bg-slate-100 dark:hover:bg-slate-800 transition"
                                title="Cài đặt kênh">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="3" />
                                    <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z" />
                                </svg>
                            </a>
                        @endif
                    </div>
                </div>

                <!-- Messages Area (Scrollable Feed) -->
                <div id="messagesContainer" x-ref="messagesContainer" class="flex-1 overflow-y-auto p-4 sm:p-6 space-y-4">

                    <!-- Welcome Banner -->
                    <div class="text-center py-6 border-b border-[var(--app-border)]/60 max-w-md mx-auto">
                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-400">
                            @if ($isDirect)
                                <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                            @else
                                <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="16" height="20" x="4" y="2" rx="2" ry="2"/><path d="M9 22v-4h6v4"/><path d="M8 6h.01"/><path d="M16 6h.01"/><path d="M12 6h.01"/><path d="M12 10h.01"/><path d="M12 14h.01"/><path d="M16 10h.01"/><path d="M16 14h.01"/><path d="M8 10h.01"/><path d="M8 14h.01"/></svg>
                            @endif
                        </div>
                        <h3 class="mt-2 text-sm font-bold text-[var(--app-text)]">
                            {{ $isDirect ? ('Cuộc trò chuyện với ' . ($directPartner?->name ?? 'Đồng nghiệp')) : ('Chào mừng bạn đến với #' . $currentChannel->slug) }}
                        </h3>
                        <p class="mt-0.5 text-xs text-[var(--app-muted)]">
                            Trao đổi thông tin bảo mật, minh bạch và lịch sự.
                        </p>
                    </div>

                    <!-- Dynamic Message List -->
                    <template x-for="msg in messages" :key="msg.id">
                        <div class="flex items-start gap-3 group transition-colors" :class="msg.is_me ? 'flex-row-reverse' : ''">

                            <!-- Avatar (Clickable -> Profile Card) -->
                            <button type="button" @click="openProfileCard(msg.user_id)"
                                class="h-9 w-9 shrink-0 rounded-xl overflow-hidden bg-slate-200 dark:bg-slate-700 flex items-center justify-center text-xs font-bold text-[var(--app-text)] hover:opacity-90 hover:ring-2 hover:ring-indigo-500/50 transition">
                                <template x-if="msg.sender_avatar">
                                    <img :src="msg.sender_avatar" alt="" class="h-full w-full object-cover">
                                </template>
                                <template x-if="!msg.sender_avatar">
                                    <span x-text="msg.sender_name.charAt(0)"></span>
                                </template>
                            </button>

                            <!-- Message Content & Meta -->
                            <div class="flex flex-col max-w-[80%] sm:max-w-[70%]" :class="msg.is_me ? 'items-end' : 'items-start'">

                                <!-- Sender Name & Time -->
                                <div class="flex items-center gap-1.5 mb-1 px-1">
                                    <button type="button" @click="openProfileCard(msg.user_id)" class="text-xs font-bold text-[var(--app-text)] hover:underline" x-text="msg.sender_name"></button>
                                    <template x-if="msg.sender_role === 'admin'">
                                        <span class="rounded bg-rose-500/15 px-1 py-0.2 text-[9px] font-bold text-rose-600 dark:text-rose-400">Admin</span>
                                    </template>
                                    <template x-if="msg.sender_role === 'hr'">
                                        <span class="rounded bg-indigo-500/15 px-1 py-0.2 text-[9px] font-bold text-indigo-600 dark:text-indigo-400">HR</span>
                                    </template>
                                    <span class="text-[10px] text-[var(--app-muted)] ml-1" x-text="msg.created_at_human" :title="msg.created_at_full"></span>
                                </div>

                                <!-- Message Bubble -->
                                <div class="relative rounded-2xl px-4 py-2.5 text-sm leading-relaxed break-words shadow-sm"
                                    :class="msg.is_me ?
                                        'bg-indigo-600 text-white rounded-tr-none' :
                                        'bg-slate-100 dark:bg-slate-800 text-[var(--app-text)] rounded-tl-none border border-[var(--app-border)]'">

                                    <!-- Rendered HTML (Safely escaped & markdown formatted) -->
                                    <div class="rich-chat-content whitespace-pre-wrap leading-relaxed" x-html="msg.message"></div>

                                    <!-- Attachments Display -->
                                    <template x-if="msg.attachments && msg.attachments.length > 0">
                                        <div class="mt-2.5 space-y-2 pt-2 border-t" :class="msg.is_me ? 'border-white/20' : 'border-[var(--app-border)]'">
                                            <!-- Image Grid -->
                                            <div class="flex flex-wrap gap-2">
                                                <template x-for="att in msg.attachments.filter(a => a.is_image)" :key="att.id">
                                                    <div class="relative rounded-xl overflow-hidden cursor-pointer border max-w-xs transition hover:opacity-90"
                                                         :class="msg.is_me ? 'border-white/20' : 'border-[var(--app-border)]'"
                                                         @click="previewImage(att.url + '?inline=1')">
                                                        <img :src="att.url + '?inline=1'" :alt="att.file_name" class="max-h-48 rounded-xl object-cover">
                                                    </div>
                                                </template>
                                            </div>

                                            <!-- Files List -->
                                            <div class="space-y-1">
                                                <template x-for="att in msg.attachments.filter(a => !a.is_image)" :key="att.id">
                                                    <a :href="att.url" download
                                                       class="flex items-center gap-2 p-2 rounded-xl text-xs transition"
                                                       :class="msg.is_me ? 'bg-white/10 hover:bg-white/20 text-white' : 'bg-slate-200/60 dark:bg-slate-700/60 hover:bg-slate-200 text-[var(--app-text)]'">
                                                        <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                                                        <span class="truncate flex-1 font-medium" x-text="att.file_name"></span>
                                                        <span class="text-[10px] opacity-75 shrink-0" x-text="att.file_size"></span>
                                                        <svg class="w-3.5 h-3.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                                    </a>
                                                </template>
                                            </div>
                                        </div>
                                    </template>

                                    <!-- Edited Notice -->
                                    <template x-if="msg.edited">
                                        <span class="inline-block mt-1 text-[9px] opacity-75 font-medium"
                                            :class="msg.is_me ? 'text-indigo-200' : 'text-[var(--app-muted)]'">
                                            (đã chỉnh sửa)
                                        </span>
                                    </template>
                                </div>

                                <!-- Reactions Badges -->
                                <div class="flex flex-wrap gap-1 mt-1 px-1">
                                    <template x-for="reaction in (msg.reactions || [])" :key="reaction.reaction">
                                        <button type="button" @click="toggleReaction(msg.id, reaction.reaction)"
                                                :title="reaction.users.join(', ')"
                                                class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold border transition"
                                                :class="reaction.user_reacted ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 ring-1 ring-indigo-500/30' : 'border-[var(--app-border)] bg-slate-50 dark:bg-slate-800 text-[var(--app-muted)] hover:border-slate-400'">
                                            <span x-text="reaction.reaction"></span>
                                            <span class="text-[10px]" x-text="reaction.count"></span>
                                        </button>
                                    </template>
                                </div>

                                <!-- Actions Bar on Hover -->
                                <div class="flex items-center gap-2 mt-1 px-1 opacity-0 group-hover:opacity-100 transition-opacity text-[11px] text-[var(--app-muted)]">
                                    
                                    <!-- Reaction Quick Bar -->
                                    <div class="flex items-center gap-1 bg-[var(--app-surface)] border border-[var(--app-border)] rounded-full px-1.5 py-0.5 shadow-sm">
                                        <template x-for="rEmoji in ['👍', '❤️', '😂', '😮', '😢', '🎉']" :key="rEmoji">
                                            <button type="button" @click="toggleReaction(msg.id, rEmoji)" class="hover:scale-125 transition transform px-0.5 text-xs">
                                                <span x-text="rEmoji"></span>
                                            </button>
                                        </template>
                                    </div>

                                    <template x-if="msg.can_edit">
                                        <button type="button" @click="openEditModal(msg)" class="hover:text-indigo-600 transition">
                                            Sửa
                                        </button>
                                    </template>
                                    <template x-if="msg.can_delete">
                                        <button type="button" @click="deleteMessage(msg.id)" class="hover:text-rose-600 transition">
                                            Xóa
                                        </button>
                                    </template>
                                </div>

                            </div>
                        </div>
                    </template>

                </div>

                <!-- Message Composer Area -->
                <div class="p-3 sm:p-4 border-t border-[var(--app-border)] bg-[var(--app-surface)]">
                    <template x-if="canSend">
                        <div class="space-y-2">

                            <!-- Formatting Toolbar & Attachment Button -->
                            <div class="flex items-center justify-between gap-2 px-1 text-xs text-[var(--app-muted)]">
                                <div class="flex items-center gap-1">
                                    <button type="button" @click="formatText('**', '**')" class="p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-[var(--app-text)] font-bold" title="In đậm (**text**)">
                                        B
                                    </button>
                                    <button type="button" @click="formatText('*', '*')" class="p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-[var(--app-text)] italic" title="In nghiêng (*text*)">
                                        I
                                    </button>
                                    <button type="button" @click="formatText('~~', '~~')" class="p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-[var(--app-text)] line-through" title="Gạch ngang (~~text~~)">
                                        S
                                    </button>
                                    <span class="text-slate-300 dark:text-slate-700">|</span>
                                    <button type="button" @click="prefixText('> ')" class="p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-[var(--app-text)]" title="Trích dẫn (> text)">
                                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 21c3 0 7-1 7-8V5c0-1.25-.756-2.017-2-2H4c-1.25 0-2 .75-2 1.972V11c0 1.25.75 2 2 2 1 0 1 0 1 1v1c0 1-1 2-2 2s-1 .008-1 1.031V20c0 1 0 1 1 1z"/></svg>
                                    </button>
                                    <button type="button" @click="prefixText('- ')" class="p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-[var(--app-text)]" title="Danh sách (- item)">
                                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
                                    </button>
                                </div>

                                <div class="flex items-center gap-1.5 relative">
                                    <!-- Emoji Picker Button & Popover -->
                                    <div class="relative" x-data="{ emojiOpen: false }">
                                        <button type="button" @click="emojiOpen = !emojiOpen" class="p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-[var(--app-text)] text-sm" title="Chèn biểu cảm">
                                            😊
                                        </button>
                                        <div x-show="emojiOpen" @click.outside="emojiOpen = false" x-cloak
                                             class="absolute right-0 bottom-full mb-2 w-64 p-3 rounded-2xl border border-[var(--app-border)] bg-[var(--app-surface)] shadow-2xl z-30 grid grid-cols-5 gap-2">
                                            <template x-for="emo in ['😀', '😂', '😍', '👍', '👏', '🎉', '🔥', '❤️', '🙌', '✨', '🚀', '💯', '🤔', '💪', '🙏', '👀', '✅', '☕', '🌟', '🥳']" :key="emo">
                                                <button type="button" @click="insertText(emo); emojiOpen = false" class="text-xl p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 text-center transition">
                                                    <span x-text="emo"></span>
                                                </button>
                                            </template>
                                        </div>
                                    </div>

                                    <!-- Attachment File Trigger -->
                                    <input type="file" x-ref="fileInput" @change="handleFileSelect($event)" multiple
                                           accept=".jpg,.jpeg,.png,.gif,.webp,.pdf,.doc,.docx,.xls,.xlsx,.txt,.zip" class="hidden">
                                    <button type="button" @click="$refs.fileInput.click()" class="p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-indigo-600 transition" title="Đính kèm tệp / hình ảnh (tối đa 5 tệp, 10MB/tệp)">
                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m21.44 11.05-9.19 9.19a6 6 0 0 1-8.49-8.49l8.57-8.57A4 4 0 1 1 18 8.84l-8.59 8.57a2 2 0 0 1-2.83-2.83l8.49-8.48"/></svg>
                                    </button>
                                </div>
                            </div>

                            <!-- Selected Attachments Preview Area -->
                            <div x-show="pendingFiles.length > 0" class="flex flex-wrap gap-2 p-2 rounded-xl bg-slate-100/70 dark:bg-slate-900/50 border border-[var(--app-border)]">
                                <template x-for="(file, index) in pendingFiles" :key="index">
                                    <div class="flex items-center gap-2 px-2.5 py-1.5 rounded-lg bg-[var(--app-surface)] border border-[var(--app-border)] text-xs text-[var(--app-text)] shadow-sm">
                                        <svg class="w-3.5 h-3.5 text-indigo-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                                        <span class="max-w-[120px] truncate font-medium" x-text="file.name"></span>
                                        <span class="text-[10px] text-[var(--app-muted)]" x-text="formatFileSize(file.size)"></span>
                                        <button type="button" @click="removePendingFile(index)" class="text-rose-500 hover:text-rose-700 ml-1 font-bold">&times;</button>
                                    </div>
                                </template>
                            </div>

                            <!-- Text Input and Send Button -->
                            <form @submit.prevent="sendMessage()" class="flex items-end gap-2">
                                <div class="relative flex-1">
                                    <textarea x-ref="messageInput" x-model="newMessageText" @keydown.enter.exact.prevent="sendMessage()"
                                        @keydown.shift.enter="true" rows="1" placeholder="Nhập tin nhắn... (Enter để gửi, Shift+Enter xuống dòng)"
                                        class="w-full resize-none max-h-32 px-4 py-2.5 text-sm rounded-xl border border-[var(--app-border)] bg-slate-50/50 dark:bg-slate-900/40 text-[var(--app-text)] placeholder-[var(--app-muted)] focus:outline-none focus:ring-2 focus:ring-indigo-500/40 focus:border-indigo-500 transition"></textarea>
                                </div>

                                <!-- Correct forward-pointing plane send button -->
                                <button type="submit" :disabled="sending || (!newMessageText.trim() && pendingFiles.length === 0)"
                                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-indigo-600 hover:bg-indigo-700 disabled:opacity-40 text-white shadow-md shadow-indigo-600/25 transition"
                                    title="Gửi tin nhắn">
                                    <template x-if="!sending">
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                                    </template>
                                    <template x-if="sending">
                                        <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <circle cx="12" cy="12" r="10" stroke-opacity="0.25" />
                                            <path d="M12 2a10 10 0 0 1 10 10" stroke-linecap="round" />
                                        </svg>
                                    </template>
                                </button>
                            </form>
                        </div>
                    </template>
                    <template x-if="!canSend">
                        <div class="p-3 text-center text-xs font-semibold text-rose-600 dark:text-rose-400 bg-rose-50 dark:bg-rose-950/30 rounded-xl border border-rose-200 dark:border-rose-900/40">
                            Bạn chỉ có quyền xem hoặc chưa phải là thành viên chính thức của kênh này.
                        </div>
                    </template>
                </div>
            @else
                <!-- Trạng thái chưa chọn kênh -->
                <div class="flex-1 flex flex-col items-center justify-center p-8 text-center">
                    <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 mb-4">
                        <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-[var(--app-text)]">Chưa có kênh nào được chọn</h3>
                    <p class="mt-1 text-sm text-[var(--app-muted)] max-w-sm">
                        Hãy chọn một kênh trò chuyện từ danh sách bên trái hoặc nhấn "Nhắn tin" để kết nối với đồng nghiệp.
                    </p>
                </div>
            @endif
        </div>

        <!-- MODAL: DANH SÁCH THÀNH VIÊN KÊNH -->
        @if ($currentChannel && ! $currentChannel->isDirect())
            <div x-show="membersModalOpen" x-cloak
                class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 p-4 backdrop-blur-sm"
                role="dialog" aria-modal="true">
                <div @click.outside="membersModalOpen = false"
                    class="w-full max-w-lg rounded-2xl border border-[var(--app-border)] bg-[var(--app-surface)] shadow-2xl flex flex-col max-h-[85vh] overflow-hidden">

                    <div class="px-6 py-4 border-b border-[var(--app-border)] flex items-center justify-between">
                        <div>
                            <h3 class="text-base font-bold text-[var(--app-text)]">Thành viên #{{ $currentChannel->slug }}</h3>
                            <p class="text-xs text-[var(--app-muted)]">{{ $currentChannel->members->count() }} thành viên đang tham gia</p>
                        </div>
                        <button type="button" @click="membersModalOpen = false"
                            class="p-1 rounded-lg text-[var(--app-muted)] hover:bg-slate-100 dark:hover:bg-slate-800">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                        </button>
                    </div>

                    <div class="flex-1 overflow-y-auto p-6 space-y-4">
                        @if (auth()->user()->can('manageMembers', $currentChannel))
                            <form action="{{ route('chat.channels.members.store', $currentChannel->slug) }}" method="POST"
                                class="p-4 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-[var(--app-border)] space-y-3">
                                @csrf
                                <p class="text-xs font-bold uppercase tracking-wider text-indigo-600 dark:text-indigo-400">
                                    + Thêm nhân sự vào kênh</p>

                                <div>
                                    <label class="block text-xs font-medium text-[var(--app-text)] mb-1">
                                        @if ($currentChannel->type === 'department' && $currentChannel->department)
                                            Chọn nhân sự phòng ban {{ $currentChannel->department->name }}
                                        @else
                                            Chọn nhân sự
                                        @endif
                                    </label>
                                    <select name="user_ids[]" multiple
                                        class="w-full rounded-xl border border-[var(--app-border)] bg-[var(--app-surface)] px-3 py-2 text-xs text-[var(--app-text)] focus:ring-2 focus:ring-indigo-500/40 h-28">
                                        @php
                                            $currentMemberIds = $currentChannel->members->pluck('user_id')->all();
                                            $channelDeptId = $currentChannel->type === 'department' ? $currentChannel->department_id : null;
                                            $eligibleUsers = $allUsers->filter(function($u) use ($channelDeptId, $currentMemberIds) {
                                                if (in_array($u->id, $currentMemberIds)) return false;
                                                if ($channelDeptId) {
                                                    return $u->employee?->department_id == $channelDeptId;
                                                }
                                                return true;
                                            });
                                        @endphp
                                        @foreach ($eligibleUsers as $u)
                                            <option value="{{ $u->id }}">
                                                {{ $u->name }} ({{ $u->employee?->department?->name ?? 'Chưa phân phòng' }} — {{ $u->role }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <p class="mt-1 text-[11px] text-[var(--app-muted)]">Giữ Ctrl (hoặc Cmd) để chọn nhiều người cùng lúc.</p>
                                </div>

                                <button type="submit"
                                    class="w-full py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow transition">
                                    Xác nhận thêm vào kênh
                                </button>
                            </form>
                        @endif

                        <div class="space-y-2">
                            <p class="text-xs font-bold text-[var(--app-muted)] uppercase tracking-wider">Danh sách hiện tại</p>
                            <div class="divide-y divide-[var(--app-border)] border border-[var(--app-border)] rounded-xl overflow-hidden">
                                @foreach ($currentChannel->members as $member)
                                    <div class="p-3 flex items-center justify-between bg-[var(--app-surface)] hover:bg-slate-50 dark:hover:bg-slate-800/50 transition">
                                        <div class="flex items-center gap-3 min-w-0">
                                            <button type="button" @click="openProfileCard({{ $member->user_id }})" class="h-8 w-8 rounded-lg overflow-hidden bg-slate-200 dark:bg-slate-700 flex items-center justify-center text-xs font-bold shrink-0">
                                                @if ($member->user?->avatar_url)
                                                    <img src="{{ $member->user->avatar_url }}" alt="" class="h-full w-full object-cover">
                                                @else
                                                    {{ substr($member->user?->name ?? 'U', 0, 1) }}
                                                @endif
                                            </button>
                                            <div class="min-w-0">
                                                <button type="button" @click="openProfileCard({{ $member->user_id }})" class="text-xs font-bold text-[var(--app-text)] truncate hover:underline block text-left">
                                                    {{ $member->user?->name }}
                                                </button>
                                                <p class="text-[11px] text-[var(--app-muted)] truncate">
                                                    {{ $member->user?->employee?->department?->name ?? 'Không phân phòng' }} • {{ $member->user?->role }}
                                                </p>
                                            </div>
                                        </div>

                                        @if (auth()->user()->can('manageMembers', $currentChannel) && $member->user_id !== auth()->id())
                                            <form action="{{ route('chat.channels.members.destroy', ['channel' => $currentChannel->slug, 'user' => $member->user_id]) }}" method="POST" onsubmit="return confirm('Bạn có chắc muốn xóa nhân sự này khỏi kênh?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-1.5 rounded-lg text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/30 transition" title="Xóa khỏi kênh">
                                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- MODAL: BẮT ĐẦU TIN NHẮN RIÊNG 1-1 -->
        <div x-show="directModalOpen" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 p-4 backdrop-blur-sm"
            role="dialog" aria-modal="true">
            <div @click.outside="directModalOpen = false"
                class="w-full max-w-md rounded-2xl border border-[var(--app-border)] bg-[var(--app-surface)] shadow-2xl flex flex-col max-h-[80vh] overflow-hidden">
                <div class="px-6 py-4 border-b border-[var(--app-border)] flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-bold text-[var(--app-text)]">Nhắn tin trực tiếp</h3>
                        <p class="text-xs text-[var(--app-muted)]">Chọn đồng nghiệp để bắt đầu trao đổi riêng</p>
                    </div>
                    <button type="button" @click="directModalOpen = false" class="p-1 rounded-lg text-[var(--app-muted)] hover:bg-slate-100 dark:hover:bg-slate-800">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                </div>

                <div class="p-4 border-b border-[var(--app-border)]">
                    <input type="text" x-model="directSearchQuery" placeholder="Tìm tên đồng nghiệp, email, phòng ban..."
                        class="w-full px-3.5 py-2 text-xs rounded-xl border border-[var(--app-border)] bg-[var(--app-surface)] text-[var(--app-text)] focus:ring-2 focus:ring-indigo-500/40">
                </div>

                <div class="flex-1 overflow-y-auto divide-y divide-[var(--app-border)] p-2">
                    @foreach ($allUsers as $colleague)
                        <form action="{{ route('chat.direct.open', $colleague->id) }}" method="POST"
                              x-show="!directSearchQuery || '{{ strtolower($colleague->name . ' ' . $colleague->email . ' ' . ($colleague->employee?->department?->name ?? '')) }}'.includes(directSearchQuery.toLowerCase())">
                            @csrf
                            <button type="submit" class="w-full p-2.5 flex items-center gap-3 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 transition text-left">
                                <div class="h-9 w-9 rounded-xl overflow-hidden bg-slate-200 dark:bg-slate-700 flex items-center justify-center text-xs font-bold shrink-0">
                                    @if ($colleague->avatar_url)
                                        <img src="{{ $colleague->avatar_url }}" alt="" class="h-full w-full object-cover">
                                    @else
                                        {{ substr($colleague->name, 0, 1) }}
                                    @endif
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-xs font-bold text-[var(--app-text)] truncate">{{ $colleague->name }}</p>
                                    <p class="text-[11px] text-[var(--app-muted)] truncate">{{ $colleague->employee?->position ?? 'Nhân viên' }} • {{ $colleague->employee?->department?->name ?? 'Không phòng ban' }}</p>
                                </div>
                                <svg class="w-4 h-4 text-[var(--app-muted)] shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
                            </button>
                        </form>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- MODAL: THẺ HỒ SƠ DANH THIẾP NHÂN SỰ AN TOÀN -->
        <div x-show="profileCardOpen" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 p-4 backdrop-blur-sm"
            role="dialog" aria-modal="true">
            <div @click.outside="profileCardOpen = false"
                class="w-full max-w-sm rounded-2xl border border-[var(--app-border)] bg-[var(--app-surface)] shadow-2xl overflow-hidden p-6 space-y-4">
                
                <template x-if="profileLoading">
                    <div class="py-8 text-center text-xs text-[var(--app-muted)]">
                        <svg class="h-6 w-6 animate-spin mx-auto text-indigo-600 mb-2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10" stroke-opacity="0.25"/><path d="M12 2a10 10 0 0 1 10 10" stroke-linecap="round"/></svg>
                        Đang tải thông tin...
                    </div>
                </template>

                <template x-if="!profileLoading && profileData">
                    <div class="space-y-4 text-center">
                        <div class="relative mx-auto h-20 w-20 rounded-2xl overflow-hidden bg-slate-200 dark:bg-slate-700 ring-4 ring-indigo-500/20 shadow-md flex items-center justify-center text-2xl font-bold">
                            <template x-if="profileData.avatar_url">
                                <img :src="profileData.avatar_url" alt="" class="h-full w-full object-cover">
                            </template>
                            <template x-if="!profileData.avatar_url">
                                <span x-text="profileData.name.charAt(0)"></span>
                            </template>
                        </div>

                        <div>
                            <h3 class="text-base font-bold text-[var(--app-text)]" x-text="profileData.name"></h3>
                            <p class="text-xs text-[var(--app-muted)] mt-0.5" x-text="profileData.position + ' • ' + profileData.department"></p>
                            <span class="inline-block mt-2 rounded-full bg-indigo-500/15 px-2.5 py-0.5 text-[10px] font-bold text-indigo-600 dark:text-indigo-400" x-text="profileData.role_label"></span>
                        </div>

                        <!-- Safe Contact Info (NO Sensitive Data) -->
                        <div class="rounded-xl border border-[var(--app-border)] bg-slate-50/50 dark:bg-slate-900/30 p-3 space-y-2 text-left text-xs">
                            <div class="flex items-center justify-between">
                                <span class="text-[var(--app-muted)]">Email:</span>
                                <span class="font-medium text-[var(--app-text)] truncate max-w-[180px]" x-text="profileData.email"></span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-[var(--app-muted)]">Điện thoại:</span>
                                <span class="font-medium text-[var(--app-text)]" x-text="profileData.phone"></span>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 pt-2">
                            <template x-if="profileData.can_dm">
                                <form :action="profileData.dm_url" method="POST" class="flex-1">
                                    @csrf
                                    <button type="submit" class="w-full py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow transition flex items-center justify-center gap-1.5">
                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                                        Nhắn tin
                                    </button>
                                </form>
                            </template>
                            <button type="button" @click="profileCardOpen = false" class="px-4 py-2.5 rounded-xl border border-[var(--app-border)] text-xs font-semibold text-[var(--app-text)] hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                                Đóng
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- MODAL: XEM ẢNH FULL SIZE -->
        <div x-show="imagePreviewOpen" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/90 p-4 backdrop-blur-md"
            @click="imagePreviewOpen = false">
            <div class="relative max-w-4xl max-h-[90vh] flex items-center justify-center" @click.stop>
                <img :src="imagePreviewSrc" alt="" class="max-w-full max-h-[85vh] rounded-2xl object-contain shadow-2xl">
                <button type="button" @click="imagePreviewOpen = false" class="absolute top-3 right-3 p-2 rounded-full bg-slate-900/80 text-white hover:bg-black transition">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>
        </div>

        <!-- MODAL: CHỈNH SỬA TIN NHẮN -->
        <div x-show="editModalOpen" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 p-4 backdrop-blur-sm"
            role="dialog" aria-modal="true">
            <div @click.outside="editModalOpen = false"
                class="w-full max-w-md rounded-2xl border border-[var(--app-border)] bg-[var(--app-surface)] p-6 shadow-2xl space-y-4">
                <h3 class="text-base font-bold text-[var(--app-text)]">Chỉnh sửa tin nhắn</h3>
                <div>
                    <textarea x-model="editingText" rows="4"
                        class="w-full rounded-xl border border-[var(--app-border)] bg-[var(--app-surface)] p-3 text-sm text-[var(--app-text)] focus:ring-2 focus:ring-indigo-500/40"></textarea>
                </div>
                <div class="flex items-center justify-end gap-3">
                    <button type="button" @click="editModalOpen = false"
                        class="px-4 py-2 rounded-xl border border-[var(--app-border)] text-xs font-semibold text-[var(--app-text)]">Hủy</button>
                    <button type="button" @click="saveEditMessage()"
                        class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow transition">Lưu thay đổi</button>
                </div>
            </div>
        </div>

    </div>

    @push('scripts')
        <script>
            function chatComponent(config) {
                return {
                    channelId: config.channelId,
                    channelSlug: config.channelSlug,
                    isCompany: config.isCompany,
                    isDirect: config.isDirect,
                    canSend: config.canSend,
                    messages: config.initialMessages,
                    currentUserId: config.currentUserId,
                    csrfToken: config.csrfToken,
                    routes: config.routes,

                    searchQuery: '',
                    newMessageText: '',
                    pendingFiles: [],
                    sending: false,
                    pollTimer: null,
                    membersModalOpen: false,
                    directModalOpen: false,
                    directSearchQuery: '',

                    profileCardOpen: false,
                    profileLoading: false,
                    profileData: null,

                    imagePreviewOpen: false,
                    imagePreviewSrc: '',

                    editModalOpen: false,
                    editingMessageId: null,
                    editingText: '',

                    initChat() {
                        this.$nextTick(() => this.scrollToBottom());

                        if (this.channelSlug) {
                            this.pollTimer = setInterval(() => this.fetchNewMessages(), 3500);
                        }

                        window.addEventListener('beforeunload', () => {
                            clearInterval(this.pollTimer);
                        });
                    },

                    scrollToBottom() {
                        const container = this.$refs.messagesContainer;
                        if (container) {
                            container.scrollTop = container.scrollHeight;
                        }
                    },

                    formatFileSize(bytes) {
                        if (bytes >= 1048576) return (bytes / 1048576).toFixed(1) + ' MB';
                        if (bytes >= 1024) return (bytes / 1024).toFixed(1) + ' KB';
                        return bytes + ' B';
                    },

                    formatText(startTag, endTag) {
                        const input = this.$refs.messageInput;
                        if (!input) return;
                        const start = input.selectionStart || 0;
                        const end = input.selectionEnd || 0;
                        const text = this.newMessageText;
                        const selected = text.substring(start, end);
                        this.newMessageText = text.substring(0, start) + startTag + selected + endTag + text.substring(end);
                        this.$nextTick(() => {
                            input.focus();
                            input.setSelectionRange(start + startTag.length, end + startTag.length);
                        });
                    },

                    prefixText(prefix) {
                        const input = this.$refs.messageInput;
                        if (!input) return;
                        const start = input.selectionStart || 0;
                        const text = this.newMessageText;
                        const lastNewLine = text.lastIndexOf('\n', start - 1);
                        const insertPos = lastNewLine === -1 ? 0 : lastNewLine + 1;
                        this.newMessageText = text.substring(0, insertPos) + prefix + text.substring(insertPos);
                        this.$nextTick(() => input.focus());
                    },

                    insertText(str) {
                        const input = this.$refs.messageInput;
                        if (!input) {
                            this.newMessageText += str;
                            return;
                        }
                        const start = input.selectionStart || 0;
                        const end = input.selectionEnd || 0;
                        const text = this.newMessageText;
                        this.newMessageText = text.substring(0, start) + str + text.substring(end);
                        this.$nextTick(() => {
                            input.focus();
                            input.setSelectionRange(start + str.length, start + str.length);
                        });
                    },

                    handleFileSelect(e) {
                        const files = Array.from(e.target.files);
                        if (this.pendingFiles.length + files.length > 5) {
                            alert('Bạn chỉ có thể đính kèm tối đa 5 tệp cho một tin nhắn.');
                            return;
                        }
                        this.pendingFiles.push(...files);
                        e.target.value = '';
                    },

                    removePendingFile(index) {
                        this.pendingFiles.splice(index, 1);
                    },

                    previewImage(url) {
                        this.imagePreviewSrc = url;
                        this.imagePreviewOpen = true;
                    },

                    openProfileCard(userId) {
                        this.profileLoading = true;
                        this.profileCardOpen = true;
                        this.profileData = null;

                        fetch(`/chat/users/${userId}/profile-card`, {
                            headers: { 'Accept': 'application/json' }
                        })
                        .then(res => {
                            if (!res.ok) throw new Error('Không thể tải hồ sơ');
                            return res.json();
                        })
                        .then(data => {
                            this.profileData = data;
                        })
                        .catch(err => {
                            alert(err.message);
                            this.profileCardOpen = false;
                        })
                        .finally(() => {
                            this.profileLoading = false;
                        });
                    },

                    toggleReaction(messageId, reaction) {
                        fetch(`/chat/messages/${messageId}/reactions`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': this.csrfToken,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({ reaction: reaction })
                        })
                        .then(res => {
                            if (!res.ok) throw new Error('Lỗi cập nhật biểu cảm');
                            return res.json();
                        })
                        .then(data => {
                            const msg = this.messages.find(m => m.id === messageId);
                            if (msg) {
                                msg.reactions = data.reactions;
                            }
                        })
                        .catch(err => console.error(err));
                    },

                    fetchNewMessages() {
                        if (!this.routes.getMessages || this.messages.length === 0) return;
                        const lastId = this.messages[this.messages.length - 1].id;

                        fetch(`${this.routes.getMessages}?after_id=${lastId}`, {
                            headers: { 'Accept': 'application/json' }
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.messages && data.messages.length > 0) {
                                data.messages.forEach(msg => {
                                    if (!this.messages.some(m => m.id === msg.id)) {
                                        this.messages.push(msg);
                                    }
                                });
                                this.$nextTick(() => this.scrollToBottom());
                                fetch(this.routes.markAsRead, {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': this.csrfToken,
                                        'Accept': 'application/json'
                                    }
                                }).catch(() => {});
                            }
                        })
                        .catch(() => {});
                    },

                    sendMessage() {
                        const text = this.newMessageText.trim();
                        if ((!text && this.pendingFiles.length === 0) || this.sending || !this.canSend) return;

                        this.sending = true;

                        const formData = new FormData();
                        formData.append('message', text);
                        this.pendingFiles.forEach(file => {
                            formData.append('attachments[]', file);
                        });

                        fetch(this.routes.sendMessage, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': this.csrfToken,
                                'Accept': 'application/json'
                            },
                            body: formData
                        })
                        .then(res => {
                            if (!res.ok) return res.json().then(e => { throw new Error(e.message || 'Lỗi gửi tin nhắn'); });
                            return res.json();
                        })
                        .then(data => {
                            this.newMessageText = '';
                            this.pendingFiles = [];
                            if (data.message && !this.messages.some(m => m.id === data.message.id)) {
                                this.messages.push(data.message);
                            }
                            this.$nextTick(() => this.scrollToBottom());
                        })
                        .catch(err => {
                            alert(err.message);
                        })
                        .finally(() => {
                            this.sending = false;
                            if (this.$refs.messageInput) {
                                this.$refs.messageInput.focus();
                            }
                        });
                    },

                    openEditModal(msg) {
                        this.editingMessageId = msg.id;
                        this.editingText = msg.raw_message || msg.message;
                        this.editModalOpen = true;
                    },

                    saveEditMessage() {
                        const text = this.editingText.trim();
                        if (!text || !this.editingMessageId) return;

                        fetch(`/chat/messages/${this.editingMessageId}`, {
                            method: 'PATCH',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': this.csrfToken,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({ message: text })
                        })
                        .then(res => {
                            if (!res.ok) throw new Error('Lỗi chỉnh sửa tin nhắn');
                            return res.json();
                        })
                        .then(data => {
                            const target = this.messages.find(m => m.id === this.editingMessageId);
                            if (target) {
                                target.message = data.message.message;
                                target.raw_message = data.message.raw_message;
                                target.edited = true;
                            }
                            this.editModalOpen = false;
                        })
                        .catch(err => alert(err.message));
                    },

                    deleteMessage(id) {
                        if (!confirm('Bạn có chắc chắn muốn xóa tin nhắn này?')) return;

                        fetch(`/chat/messages/${id}`, {
                            method: 'DELETE',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': this.csrfToken,
                                'Accept': 'application/json'
                            }
                        })
                        .then(res => {
                            if (!res.ok) throw new Error('Không thể xóa tin nhắn');
                            this.messages = this.messages.filter(m => m.id !== id);
                        })
                        .catch(err => alert(err.message));
                    }
                };
            }
        </script>
    @endpush
@endsection
