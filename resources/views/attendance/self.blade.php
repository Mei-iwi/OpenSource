@extends('layouts.app')
@section('title', 'Chấm công của tôi')
@section('content')
<div class="mx-auto max-w-5xl">
    <div class="mb-6 flex flex-wrap items-end justify-between gap-3">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.14em] text-indigo-600 dark:text-indigo-400">Cá nhân</p>
            <h1 class="mt-1 text-3xl font-bold tracking-tight text-[var(--app-text)]">Chấm công</h1>
        </div>
        <p class="text-sm text-[var(--app-muted)]">{{ now($schedule['timezone'])->translatedFormat('d/m/Y') }}</p>
    </div>

@if (! $employee)
    <div class="app-panel p-8 text-center">
        <x-empty-state title="Chưa có hồ sơ nhân viên" description="Tài khoản chưa được gắn với hồ sơ nhân viên nên chưa thể chấm công." />
    </div>
@else
    @if($errors->any())
        <div class="app-panel border border-rose-300 p-4 text-rose-700" role="alert">{{ $errors->first() }}</div>
    @endif
    <div x-data="selfAttendance()" class="space-y-6">
        @include('attendance._summary')

        <div x-show="noticeOpen" x-cloak class="pointer-events-none fixed inset-x-4 top-4 z-[80] flex justify-center sm:inset-x-0" role="alertdialog" aria-modal="true" aria-labelledby="attendance-notice-title" aria-describedby="attendance-notice-text" @keydown.escape.window="finishNotice()">
            <div class="glass-island-panel pointer-events-auto flex w-full max-w-md items-start gap-3 rounded-[1.75rem] p-4 shadow-2xl">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full" :class="noticeKind === 'success' ? 'bg-indigo-500/10 text-indigo-600' : 'bg-rose-500/10 text-rose-600'" aria-hidden="true">
                    <svg x-show="noticeKind === 'success'" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m5 12 4 4L19 6"/></svg>
                    <svg x-show="noticeKind === 'error'" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 8v5M12 16h.01"/></svg>
                </div>
                <div class="min-w-0 flex-1"><h2 id="attendance-notice-title" class="font-bold" x-text="noticeTitle"></h2><p id="attendance-notice-text" class="mt-1 text-sm text-[var(--app-muted)]" x-text="noticeText"></p></div>
                <button type="button" class="rounded-full px-2 py-1 text-sm font-semibold text-[var(--app-accent)]" @click="finishNotice()" x-text="noticeKind === 'success' ? 'Xong' : 'Đóng'"></button>
            </div>
        </div>

        <!-- Camera / Photo Modal Dialog -->
        <div x-show="openModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 p-4 backdrop-blur-md" role="dialog" aria-modal="true" aria-labelledby="attendance-modal-title">
            <div @click.outside="close()" class="w-full max-w-lg rounded-3xl border border-[var(--app-border)] bg-[var(--app-surface)] p-6 shadow-2xl">
                <div class="flex items-start justify-between gap-4 border-b border-[var(--app-border)] pb-4">
                    <div>
                        <h2 id="attendance-modal-title" class="text-lg font-bold text-[var(--app-text)]" x-text="action === 'check-in' ? 'Chấm công vào' : 'Chấm công ra'"></h2>
                        <p class="mt-1 text-xs text-[var(--app-muted)]">Chụp rõ khuôn mặt.</p>
                    </div>
                    <button type="button" @click="close()" class="flex h-8 w-8 items-center justify-center rounded-full text-slate-400 hover:bg-slate-100 hover:text-[var(--app-text)] dark:hover:bg-slate-800" aria-label="Đóng">&times;</button>
                </div>

                <div class="mt-5">
                    <button type="button" @click="startCamera()" class="app-button-secondary w-full py-3">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14.5 4h-5L7 7H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-3l-2.5-3z"/><circle cx="12" cy="13" r="3"/></svg>
                        <span>Mở Camera</span>
                    </button>
                </div>

                <p x-show="cameraError" x-text="cameraError" class="mt-3 text-xs font-semibold text-amber-600 dark:text-amber-400" role="alert"></p>

                <!-- Active Camera Stream View -->
                <div x-show="cameraActive" class="mt-4 space-y-3">
                    <div class="relative overflow-hidden rounded-2xl bg-slate-950 border border-white/10">
                        <video x-ref="video" autoplay playsinline class="max-h-72 w-full object-cover"></video>
                        <div class="absolute inset-0 pointer-events-none flex items-center justify-center">
                            <div class="h-48 w-48 rounded-full border-2 border-dashed border-white/40"></div>
                        </div>
                    </div>
                    <button type="button" @click="capture()" class="app-button-primary w-full py-3">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="3"/></svg>
                        <span>Chụp ảnh ngay</span>
                    </button>
                </div>

                <!-- Preview captured camera photo -->
                <div x-show="previewUrl" class="mt-4 space-y-4">
                    <div class="overflow-hidden rounded-2xl border border-[var(--app-border)] bg-slate-950">
                        <img :src="previewUrl" alt="Ảnh xem trước" class="max-h-72 w-full object-contain">
                    </div>
                    <div class="flex justify-end gap-3">
                        <button type="button" @click="resetPhoto()" class="app-button-secondary">
                            Chụp lại
                        </button>
                        <button type="button" @click="submit()" :disabled="submitting" class="app-button-primary">
                            <span x-text="submitting ? 'Đang xác thực...' : 'Xác nhận chấm công'"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif
</div>
@endsection

@push('scripts')
<script>
function selfAttendance() {
    return {
        openModal: false, action: '', previewUrl: '', photo: null, stream: null, cameraActive: false, cameraError: '', submitting: false,
        noticeOpen: false, noticeKind: '', noticeTitle: '', noticeText: '',
        showNotice(kind, title, text) { this.noticeKind = kind; this.noticeTitle = title; this.noticeText = text; this.noticeOpen = true; },
        finishNotice() { if (this.noticeKind === 'success') { window.location.reload(); return; } this.noticeOpen = false; },
        open(action) { this.action = action; this.openModal = true; this.resetPhoto(); },
        close() { this.stopCamera(); this.openModal = false; this.resetPhoto(); },
        async startCamera() {
            this.resetPhoto();
            this.cameraError = '';
            if (!navigator.mediaDevices?.getUserMedia) {
                this.cameraError = 'Không thể truy cập camera. Vui lòng dùng thiết bị có camera và kết nối HTTPS.';
                return;
            }
            try {
                this.stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user' }, audio: false });
                this.$refs.video.srcObject = this.stream;
                this.cameraActive = true;
            } catch (error) {
                this.cameraError = 'Không thể truy cập camera. Vui lòng cấp quyền và thử lại.';
                this.stopCamera();
            }
        },
        capture() {
            const video = this.$refs.video;
            const canvas = document.createElement('canvas');
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            canvas.getContext('2d').drawImage(video, 0, 0);
            canvas.toBlob((blob) => {
                this.photo = new File([blob], 'camera-capture.jpg', { type: 'image/jpeg' });
                this.previewUrl = URL.createObjectURL(blob);
                this.stopCamera();
            }, 'image/jpeg', .9);
        },
        resetPhoto() {
            this.stopCamera();
            if (this.previewUrl) URL.revokeObjectURL(this.previewUrl);
            this.previewUrl = '';
            this.photo = null;
        },
        stopCamera() {
            if (this.stream) this.stream.getTracks().forEach(track => track.stop());
            this.stream = null;
            this.cameraActive = false;
        },
        async submit() {
            if (!this.photo || this.submitting) return;
            this.submitting = true;
            this.cameraError = '';
            try {
                const form = new FormData();
                form.append('photo', this.photo);
                form.append('method', 'camera');
                if ({{ config('attendance.enforce_location') ? 'true' : 'false' }}) {
                    if (!navigator.geolocation) throw new Error('Thiết bị không hỗ trợ định vị.');
                    const position = await new Promise((resolve, reject) => navigator.geolocation.getCurrentPosition(resolve, reject, {
                        enableHighAccuracy: true, timeout: 15000, maximumAge: 0
                    }));
                    form.append('latitude', position.coords.latitude);
                    form.append('longitude', position.coords.longitude);
                    form.append('accuracy', position.coords.accuracy);
                }
                const response = await fetch(this.action === 'check-in' ? '{{ route('me.attendance.check-in') }}' : '{{ route('me.attendance.check-out') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: form
                });
                if (response.redirected && response.url.includes('/login')) {
                    window.location.href = response.url;
                    return;
                }
                const data = await response.json().catch(() => null);
                if (!response.ok) {
                    throw new Error(Object.values(data?.errors || {}).flat()[0] || data?.message || 'Không thể lưu chấm công. Vui lòng thử lại.');
                }
                if (!data?.recorded_at) throw new Error('Không nhận được giờ chấm công từ máy chủ.');
                const completedAction = this.action;
                this.close();
                this.showNotice('success', completedAction === 'check-in' ? 'Chấm công vào thành công' : 'Chấm công ra thành công', `${data.message} Lúc ${data.recorded_at}.`);
            } catch (error) {
                this.submitting = false;
                this.showNotice('error', 'Chấm công chưa thành công', error?.message || 'Vui lòng thử lại.');
            }
        }
    };
}
</script>
@endpush
