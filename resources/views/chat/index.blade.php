@extends('layouts.app')

@section('title', $currentChannel ? '#' . $currentChannel->slug . ' — Chat nội bộ' : 'Chat nội bộ')

@section('content')
<div class="h-[calc(100vh-8.5rem)] flex flex-col md:flex-row rounded-2xl border border-[var(--app-border)] bg-[var(--app-surface)] shadow-xl overflow-hidden"
     x-data="chatComponent({
        channelId: {{ $currentChannel ? $currentChannel->id : 'null' }},
        channelSlug: '{{ $currentChannel ? $currentChannel->slug : '' }}',
        isCompany: {{ $currentChannel && $currentChannel->isCompany() ? 'true' : 'false' }},
        canSend: {{ $currentChannel && auth()->user()->can('sendMessage', $currentChannel) ? 'true' : 'false' }},
        initialMessages: @js($messages->map(fn($m) => [
            'id' => $m->id,
            'user_id' => $m->user_id,
            'sender_name' => $m->user?->name ?? 'Người dùng',
            'sender_avatar' => $m->user?->avatar_url,
            'sender_role' => $m->user?->role,
            'sender_position' => $m->user?->employee?->position,
            'sender_department' => $m->user?->employee?->department?->name,
            'message' => $m->message,
            'is_me' => $m->user_id === auth()->id(),
            'can_edit' => auth()->user()->can('update', $m),
            'can_delete' => auth()->user()->can('delete', $m),
            'edited' => $m->isEdited(),
            'created_at_human' => $m->created_at?->format('H:i') ?? '',
            'created_at_full' => $m->created_at?->format('d/m/Y H:i') ?? '',
        ])->values()),
        currentUserId: {{ auth()->id() }},
        csrfToken: '{{ csrf_token() }}',
        routes: {
            getMessages: '{{ $currentChannel ? route('chat.channels.messages.index', $currentChannel->slug) : '' }}',
            sendMessage: '{{ $currentChannel ? route('chat.channels.messages.store', $currentChannel->slug) : '' }}',
            markAsRead: '{{ $currentChannel ? route('chat.channels.read', $currentChannel->slug) : '' }}',
            unreadSummary: '{{ route('chat.unread-summary') }}',
        }
     })"
     x-init="initChat()">

    <!-- LEFT SIDEBAR: DANH SÁCH CHANNEL -->
    <div class="w-full md:w-80 lg:w-96 flex-shrink-0 flex flex-col border-b md:border-b-0 md:border-r border-[var(--app-border)] bg-slate-50/50 dark:bg-slate-900/30">
        
        <!-- Header & Search -->
        <div class="p-4 border-b border-[var(--app-border)] space-y-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-400">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M7.9 20A9 9 0 1 0 4 16.1L2 22Z" />
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-base font-bold text-[var(--app-text)] leading-tight">Kênh Trò Chuyện</h1>
                        <p class="text-xs text-[var(--app-muted)]">Giao tiếp nội bộ công ty</p>
                    </div>
                </div>

                @if(auth()->user()->isAdmin() || auth()->user()->isHr())
                    <a href="{{ route('chat.channels.create') }}"
                       class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-sm transition-all"
                       title="Tạo kênh mới">
                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        <span>Tạo kênh</span>
                    </a>
                @endif
            </div>

            <!-- Search box -->
            <div class="relative">
                <input type="text"
                       x-model="searchQuery"
                       placeholder="Tìm kiếm kênh..."
                       class="w-full pl-9 pr-3 py-2 text-xs rounded-xl border border-[var(--app-border)] bg-[var(--app-surface)] text-[var(--app-text)] placeholder-[var(--app-muted)] focus:outline-none focus:ring-2 focus:ring-indigo-500/40">
                <svg class="absolute left-3 top-2.5 h-4 w-4 text-[var(--app-muted)]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            </div>
        </div>

        <!-- Channels Grouped List -->
        <div class="flex-1 overflow-y-auto p-3 space-y-4">
            
            <!-- Nhóm: Toàn công ty -->
            <div>
                <p class="px-2.5 text-[10px] font-extrabold uppercase tracking-wider text-[var(--app-muted)] flex items-center gap-1.5">
                    <span>🌐</span>
                    <span>Toàn Công Ty</span>
                </p>
                <div class="mt-1.5 space-y-1">
                    @foreach($channels->filter(fn($c) => $c->isCompany()) as $ch)
                        @include('chat.partials.channel-item', ['channel' => $ch, 'active' => $currentChannel && $currentChannel->id === $ch->id])
                    @endforeach
                </div>
            </div>

            <!-- Nhóm: Phòng ban -->
            @php $deptChannels = $channels->filter(fn($c) => !$c->isCompany() && $c->type === 'department'); @endphp
            @if($deptChannels->isNotEmpty())
                <div>
                    <p class="px-2.5 text-[10px] font-extrabold uppercase tracking-wider text-[var(--app-muted)] flex items-center gap-1.5">
                        <span>🏢</span>
                        <span>Phòng Ban</span>
                    </p>
                    <div class="mt-1.5 space-y-1">
                        @foreach($deptChannels as $ch)
                            @include('chat.partials.channel-item', ['channel' => $ch, 'active' => $currentChannel && $currentChannel->id === $ch->id])
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Nhóm: Nhóm dự án / Riêng tư -->
            @php $groupChannels = $channels->filter(fn($c) => !$c->isCompany() && $c->type !== 'department'); @endphp
            @if($groupChannels->isNotEmpty())
                <div>
                    <p class="px-2.5 text-[10px] font-extrabold uppercase tracking-wider text-[var(--app-muted)] flex items-center gap-1.5">
                        <span>👥</span>
                        <span>Nhóm & Dự Án</span>
                    </p>
                    <div class="mt-1.5 space-y-1">
                        @foreach($groupChannels as $ch)
                            @include('chat.partials.channel-item', ['channel' => $ch, 'active' => $currentChannel && $currentChannel->id === $ch->id])
                        @endforeach
                    </div>
                </div>
            @endif

        </div>
    </div>

    <!-- RIGHT AREA: VÙNG CHAT CHÍNH -->
    <div class="flex-1 flex flex-col min-w-0 bg-[var(--app-surface)] relative">
        @if($currentChannel)
            <!-- Channel Header -->
            <div class="h-16 flex-shrink-0 px-4 sm:px-6 flex items-center justify-between border-b border-[var(--app-border)] bg-[var(--app-surface)] z-10">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl font-extrabold text-sm {{ $currentChannel->isCompany() ? 'bg-amber-500/10 text-amber-600 dark:text-amber-400' : ($currentChannel->type === 'department' ? 'bg-indigo-500/10 text-indigo-600 dark:text-indigo-400' : 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400') }}">
                        {{ $currentChannel->isCompany() ? '🌐' : '#' }}
                    </div>
                    <div class="min-w-0">
                        <div class="flex items-center gap-2">
                            <h2 class="text-sm sm:text-base font-bold text-[var(--app-text)] truncate">
                                #{{ $currentChannel->slug }}
                            </h2>
                            @if($currentChannel->isCompany())
                                <span class="rounded-md bg-amber-500/15 px-2 py-0.5 text-[10px] font-bold text-amber-600 dark:bg-amber-500/20 dark:text-amber-400">Toàn công ty</span>
                            @elseif($currentChannel->type === 'department')
                                <span class="rounded-md bg-indigo-500/15 px-2 py-0.5 text-[10px] font-bold text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-400">Phòng ban</span>
                            @else
                                <span class="rounded-md bg-emerald-500/15 px-2 py-0.5 text-[10px] font-bold text-emerald-600 dark:bg-emerald-500/20 dark:text-emerald-400">Nhóm</span>
                            @endif
                        </div>
                        <p class="text-xs text-[var(--app-muted)] truncate max-w-md">
                            {{ $currentChannel->description ?: 'Không có mô tả kênh.' }}
                        </p>
                    </div>
                </div>

                <!-- Channel Actions -->
                <div class="flex items-center gap-2">
                    <!-- Nút mở danh sách thành viên -->
                    <button type="button"
                            @click="membersModalOpen = true"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-[var(--app-border)] text-xs font-semibold text-[var(--app-text)] hover:bg-slate-100 dark:hover:bg-slate-800 transition-all">
                        <svg class="h-4 w-4 text-[var(--app-muted)]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        <span class="hidden sm:inline">Thành viên</span>
                        <span class="rounded-full bg-slate-200 dark:bg-slate-700 px-1.5 py-0.2 text-[10px] font-bold">{{ $currentChannel->members->count() }}</span>
                    </button>

                    <!-- Nút chỉnh sửa / xóa kênh (nếu Admin/HR và không phải kênh công ty) -->
                    @if(auth()->user()->can('update', $currentChannel))
                        <a href="{{ route('chat.channels.edit', $currentChannel->slug) }}"
                           class="inline-flex items-center p-2 rounded-xl border border-[var(--app-border)] text-[var(--app-muted)] hover:text-indigo-600 hover:bg-slate-100 dark:hover:bg-slate-800 transition-all"
                           title="Cài đặt kênh">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                        </a>
                    @endif
                </div>
            </div>

            <!-- Messages Area (Scrollable Feed) -->
            <div id="messagesContainer"
                 x-ref="messagesContainer"
                 class="flex-1 overflow-y-auto p-4 sm:p-6 space-y-4">
                
                <!-- Welcome Banner trong kênh -->
                <div class="text-center py-6 border-b border-[var(--app-border)]/60 max-w-md mx-auto">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-indigo-500/10 text-2xl">
                        {{ $currentChannel->isCompany() ? '🌐' : '💬' }}
                    </div>
                    <h3 class="mt-3 text-base font-bold text-[var(--app-text)]">Chào mừng bạn đến với #{{ $currentChannel->slug }}!</h3>
                    <p class="mt-1 text-xs text-[var(--app-muted)] leading-relaxed">
                        Đây là khởi đầu của cuộc trò chuyện trong kênh này. Hãy cùng trao đổi công việc lịch sự, chuyên nghiệp.
                    </p>
                </div>

                <!-- Dynamic Message List -->
                <template x-for="(msg, index) in messages" :key="msg.id">
                    <div class="flex items-start gap-3 group transition-colors"
                         :class="msg.is_me ? 'flex-row-reverse' : ''">
                        
                        <!-- Avatar -->
                        <div class="h-9 w-9 shrink-0 rounded-xl overflow-hidden bg-slate-200 dark:bg-slate-700 flex items-center justify-center text-xs font-bold text-[var(--app-text)]">
                            <template x-if="msg.sender_avatar">
                                <img :src="msg.sender_avatar" alt="" class="h-full w-full object-cover">
                            </template>
                            <template x-if="!msg.sender_avatar">
                                <span x-text="msg.sender_name.charAt(0)"></span>
                            </template>
                        </div>

                        <!-- Message Content & Meta -->
                        <div class="flex flex-col max-w-[75%] sm:max-w-[65%]"
                             :class="msg.is_me ? 'items-end' : 'items-start'">
                            
                            <!-- Sender Name & Time -->
                            <div class="flex items-center gap-2 mb-1 px-1">
                                <span class="text-xs font-bold text-[var(--app-text)]" x-text="msg.sender_name"></span>
                                <template x-if="msg.sender_role === 'admin'">
                                    <span class="rounded bg-rose-500/15 px-1 py-0.2 text-[9px] font-bold text-rose-600 dark:text-rose-400">Admin</span>
                                </template>
                                <template x-if="msg.sender_role === 'hr'">
                                    <span class="rounded bg-indigo-500/15 px-1 py-0.2 text-[9px] font-bold text-indigo-600 dark:text-indigo-400">HR</span>
                                </template>
                                <span class="text-[10px] text-[var(--app-muted)]" x-text="msg.created_at_human" :title="msg.created_at_full"></span>
                            </div>

                            <!-- Message Bubble -->
                            <div class="relative rounded-2xl px-4 py-2.5 text-sm leading-relaxed break-words shadow-sm"
                                 :class="msg.is_me
                                    ? 'bg-indigo-600 text-white rounded-tr-none'
                                    : 'bg-slate-100 dark:bg-slate-800 text-[var(--app-text)] rounded-tl-none border border-[var(--app-border)]'">
                                
                                <p class="whitespace-pre-wrap" x-text="msg.message"></p>
                                
                                <template x-if="msg.edited">
                                    <span class="inline-block mt-1 text-[9px] opacity-75 font-medium"
                                          :class="msg.is_me ? 'text-indigo-200' : 'text-[var(--app-muted)]'">
                                        (đã chỉnh sửa)
                                    </span>
                                </template>
                            </div>

                            <!-- Actions for own message / moderation -->
                            <div class="flex items-center gap-2 mt-1 px-1 opacity-0 group-hover:opacity-100 transition-opacity text-[11px] text-[var(--app-muted)]">
                                <template x-if="msg.can_edit">
                                    <button type="button"
                                            @click="openEditModal(msg)"
                                            class="hover:text-indigo-600 transition">
                                        Sửa
                                    </button>
                                </template>
                                <template x-if="msg.can_delete">
                                    <button type="button"
                                            @click="deleteMessage(msg.id)"
                                            class="hover:text-rose-600 transition">
                                        Xóa
                                    </button>
                                </template>
                            </div>

                        </div>
                    </div>
                </template>

            </div>

            <!-- Message Composer -->
            <div class="p-4 border-t border-[var(--app-border)] bg-[var(--app-surface)]">
                <template x-if="canSend">
                    <form @submit.prevent="sendMessage()" class="flex items-end gap-3">
                        <div class="relative flex-1">
                            <textarea x-ref="messageInput"
                                      x-model="newMessageText"
                                      @keydown.enter.exact.prevent="sendMessage()"
                                      @keydown.shift.enter="true"
                                      rows="1"
                                      placeholder="Nhập tin nhắn... (Enter để gửi, Shift+Enter xuống dòng)"
                                      class="w-full resize-none max-h-32 px-4 py-3 text-sm rounded-xl border border-[var(--app-border)] bg-slate-50/50 dark:bg-slate-900/40 text-[var(--app-text)] placeholder-[var(--app-muted)] focus:outline-none focus:ring-2 focus:ring-indigo-500/40 focus:border-indigo-500 transition-all"></textarea>
                        </div>
                        <button type="submit"
                                :disabled="sending || !newMessageText.trim()"
                                class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white shadow-lg shadow-indigo-600/25 transition-all">
                            <template x-if="!sending">
                                <svg class="h-5 w-5 transform rotate-90" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                            </template>
                            <template x-if="sending">
                                <svg class="h-5 w-5 animate-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10" stroke-opacity="0.25"/><path d="M12 2a10 10 0 0 1 10 10" stroke-linecap="round"/></svg>
                            </template>
                        </button>
                    </form>
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
                    <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                </div>
                <h3 class="text-lg font-bold text-[var(--app-text)]">Chưa có kênh nào được chọn</h3>
                <p class="mt-1 text-sm text-[var(--app-muted)] max-w-sm">
                    Hãy chọn một kênh trò chuyện từ danh sách bên trái để bắt đầu trao đổi với đồng nghiệp.
                </p>
            </div>
        @endif
    </div>

    <!-- MODAL: DANH SÁCH THÀNH VIÊN & QUẢN LÝ -->
    @if($currentChannel)
        <div x-show="membersModalOpen"
             x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 p-4 backdrop-blur-sm"
             role="dialog" aria-modal="true">
            <div @click.outside="membersModalOpen = false"
                 class="w-full max-w-lg rounded-2xl border border-[var(--app-border)] bg-[var(--app-surface)] shadow-2xl flex flex-col max-h-[85vh] overflow-hidden">
                
                <!-- Modal Header -->
                <div class="px-6 py-4 border-b border-[var(--app-border)] flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-bold text-[var(--app-text)]">Thành viên #{{ $currentChannel->slug }}</h3>
                        <p class="text-xs text-[var(--app-muted)]">{{ $currentChannel->members->count() }} thành viên đang tham gia</p>
                    </div>
                    <button type="button" @click="membersModalOpen = false" class="p-1 rounded-lg text-[var(--app-muted)] hover:bg-slate-100 dark:hover:bg-slate-800">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                </div>

                <!-- Modal Body: Tabs -->
                <div class="flex-1 overflow-y-auto p-6 space-y-4">
                    
                    @if(auth()->user()->can('manageMembers', $currentChannel))
                        <!-- Form Thêm thành viên -->
                        <form action="{{ route('chat.channels.members.store', $currentChannel->slug) }}" method="POST" class="p-4 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-[var(--app-border)] space-y-3">
                            @csrf
                            <p class="text-xs font-bold uppercase tracking-wider text-indigo-600 dark:text-indigo-400">+ Thêm nhân sự vào kênh</p>
                            
                            <div>
                                <label class="block text-xs font-medium text-[var(--app-text)] mb-1">Chọn nhân sự</label>
                                <select name="user_ids[]" multiple class="w-full rounded-xl border border-[var(--app-border)] bg-[var(--app-surface)] px-3 py-2 text-xs text-[var(--app-text)] focus:ring-2 focus:ring-indigo-500/40 h-28">
                                    @php $currentMemberIds = $currentChannel->members->pluck('user_id')->all(); @endphp
                                    @foreach($allUsers as $u)
                                        @if(!in_array($u->id, $currentMemberIds))
                                            <option value="{{ $u->id }}">
                                                {{ $u->name }} ({{ $u->employee?->department?->name ?? 'Chưa phân phòng' }} — {{ $u->role }})
                                            </option>
                                        @endif
                                    @endforeach
                                </select>
                                <p class="mt-1 text-[11px] text-[var(--app-muted)]">Giữ Ctrl (hoặc Cmd) để chọn nhiều người cùng lúc.</p>
                            </div>

                            <button type="submit" class="w-full py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow transition">
                                Xác nhận thêm vào kênh
                            </button>
                        </form>
                    @endif

                    <!-- Danh sách thành viên hiện tại -->
                    <div class="space-y-2">
                        <p class="text-xs font-bold text-[var(--app-muted)] uppercase tracking-wider">Danh sách hiện tại</p>
                        <div class="divide-y divide-[var(--app-border)] border border-[var(--app-border)] rounded-xl overflow-hidden">
                            @foreach($currentChannel->members as $member)
                                <div class="p-3 flex items-center justify-between bg-[var(--app-surface)] hover:bg-slate-50 dark:hover:bg-slate-800/50 transition">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <div class="h-8 w-8 rounded-lg overflow-hidden bg-slate-200 dark:bg-slate-700 flex items-center justify-center text-xs font-bold">
                                            @if($member->user?->avatar_url)
                                                <img src="{{ $member->user->avatar_url }}" alt="" class="h-full w-full object-cover">
                                            @else
                                                {{ substr($member->user?->name ?? 'U', 0, 1) }}
                                            @endif
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-xs font-bold text-[var(--app-text)] truncate">{{ $member->user?->name }}</p>
                                            <p class="text-[11px] text-[var(--app-muted)] truncate">
                                                {{ $member->user?->employee?->department?->name ?? 'Không phân phòng' }} • {{ $member->user?->role }}
                                            </p>
                                        </div>
                                    </div>

                                    @if(auth()->user()->can('manageMembers', $currentChannel) && $member->user_id !== auth()->id())
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

    <!-- MODAL: CHỈNH SỬA TIN NHẮN -->
    <div x-show="editModalOpen"
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 p-4 backdrop-blur-sm"
         role="dialog" aria-modal="true">
        <div @click.outside="editModalOpen = false"
             class="w-full max-w-md rounded-2xl border border-[var(--app-border)] bg-[var(--app-surface)] p-6 shadow-2xl space-y-4">
            <h3 class="text-base font-bold text-[var(--app-text)]">Chỉnh sửa tin nhắn</h3>
            <div>
                <textarea x-model="editingText"
                          rows="4"
                          class="w-full rounded-xl border border-[var(--app-border)] bg-[var(--app-surface)] p-3 text-sm text-[var(--app-text)] focus:ring-2 focus:ring-indigo-500/40"></textarea>
            </div>
            <div class="flex items-center justify-end gap-3">
                <button type="button" @click="editModalOpen = false" class="px-4 py-2 rounded-xl border border-[var(--app-border)] text-xs font-semibold text-[var(--app-text)]">Hủy</button>
                <button type="button" @click="saveEditMessage()" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow transition">Lưu thay đổi</button>
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
        canSend: config.canSend,
        messages: config.initialMessages,
        currentUserId: config.currentUserId,
        csrfToken: config.csrfToken,
        routes: config.routes,

        searchQuery: '',
        newMessageText: '',
        sending: false,
        pollTimer: null,
        unreadTimer: null,
        membersModalOpen: false,

        editModalOpen: false,
        editingMessageId: null,
        editingText: '',

        initChat() {
            this.$nextTick(() => this.scrollToBottom());

            if (this.channelSlug) {
                // Polling tin nhắn mới mỗi 3.5 giây
                this.pollTimer = setInterval(() => this.fetchNewMessages(), 3500);
            }

            // Dọn dẹp timer khi rời trang
            window.addEventListener('beforeunload', () => {
                clearInterval(this.pollTimer);
                clearInterval(this.unreadTimer);
            });
        },

        scrollToBottom() {
            const container = this.$refs.messagesContainer;
            if (container) {
                container.scrollTop = container.scrollHeight;
            }
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
                    // Đánh dấu đã đọc
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
            if (!text || this.sending || !this.canSend) return;

            this.sending = true;

            fetch(this.routes.sendMessage, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ message: text })
            })
            .then(res => {
                if (!res.ok) throw new Error('Không thể gửi tin nhắn');
                return res.json();
            })
            .then(data => {
                this.newMessageText = '';
                if (data.message && !this.messages.some(m => m.id === data.message.id)) {
                    this.messages.push(data.message);
                }
                this.$nextTick(() => this.scrollToBottom());
            })
            .catch(err => {
                alert('Lỗi gửi tin nhắn: ' + err.message);
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
            this.editingText = msg.message;
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
