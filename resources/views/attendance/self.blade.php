@extends('layouts.app')
@section('title', 'Chấm công của tôi')
@section('content')
<x-page-header eyebrow="Khu vực Cá nhân" title="Tự Chấm công Thông minh" description="Xác nhận giờ vào và giờ ra ca làm việc bằng ảnh chụp từ camera hoặc tải ảnh từ thiết bị.">
    <a href="{{ route('dashboard') }}" class="app-button-secondary">
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
        <span>Về tổng quan</span>
    </a>
</x-page-header>

@if (! $employee)
    <div class="app-panel p-8 text-center">
        <x-empty-state title="Chưa có hồ sơ nhân viên" description="Tài khoản chưa được gắn với hồ sơ nhân viên nên chưa thể chấm công." />
    </div>
@else
    <div x-data="selfAttendance()" class="space-y-6">
        <!-- Identity & Live Clock Banner -->
        <div class="app-panel flex flex-wrap items-center justify-between gap-5 p-5 sm:p-6 bg-gradient-to-r from-slate-50 via-[var(--app-surface)] to-indigo-50/25 dark:from-slate-800/40 dark:via-[var(--app-surface)] dark:to-indigo-950/25">
            <div class="flex items-center gap-4">
                <div class="flex h-16 w-16 shrink-0 items-center justify-center overflow-hidden rounded-2xl bg-gradient-to-tr from-indigo-600 via-indigo-500 to-sky-400 text-xl font-bold text-white shadow-md shadow-indigo-500/20 ring-4 ring-indigo-500/15">
                    @if ($employee->user->avatar_path)
                        <img src="{{ $employee->user->avatar_url }}" alt="Ảnh đại diện {{ $employee->user->name }}" class="h-full w-full object-cover">
                    @else
                        {{ strtoupper(substr($employee->user->name, 0, 1)) }}
                    @endif
                </div>
                <div>
                    <h2 class="text-lg font-extrabold text-[var(--app-text)] sm:text-xl">{{ $employee->user->name }}</h2>
                    <p class="text-xs font-semibold text-[var(--app-muted)] mt-0.5">
                        <span class="font-mono text-indigo-600 dark:text-indigo-400">{{ $employee->employee_code }}</span> · {{ $employee->department?->name ?? 'Chưa phân phòng ban' }}
                    </p>
                </div>
            </div>

            <div class="rounded-2xl border border-[var(--app-border)] bg-[var(--app-surface)] p-3 px-5 text-left sm:text-right shadow-xs">
                <div class="flex items-center gap-2 justify-start sm:justify-end">
                    <span class="h-2 w-2 rounded-full bg-emerald-500 animate-ping"></span>
                    <p class="text-xs font-bold uppercase tracking-wider text-[var(--app-muted)]">Thời gian thực</p>
                </div>
                <p class="mt-1 font-mono text-2xl font-black tracking-tight text-[var(--app-text)]" x-text="clock">--:--:--</p>
                <p class="text-xs font-medium text-[var(--app-muted)]">{{ now()->translatedFormat('l, d/m/Y') }}</p>
            </div>
        </div>

        <!-- Today Shift Status Card -->
        <div class="app-panel p-5 sm:p-6">
            <div class="flex flex-wrap items-start justify-between gap-4 border-b border-[var(--app-border)] pb-4">
                <div>
                    <h2 class="app-heading">Trạng thái ca làm việc hôm nay</h2>
                    <p class="app-subtitle">Ảnh xác thực và tọa độ được lưu trữ an toàn, phục vụ đối soát minh bạch.</p>
                </div>
                <span class="app-badge app-badge-info text-xs font-bold">
                    {{ $todayAttendance ? ($todayAttendance->check_out ? 'Ca làm đã kết thúc' : 'Đang trong ca làm việc') : 'Chưa điểm danh vào' }}
                </span>
            </div>

            <div class="mt-6 grid gap-4 sm:grid-cols-3">
                <!-- Check-in info -->
                <div class="rounded-2xl border border-[var(--app-border)] bg-slate-50/70 dark:bg-slate-800/40 p-4">
                    <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-[var(--app-muted)]">
                        <svg class="h-4 w-4 text-emerald-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                        <span>Giờ điểm danh vào</span>
                    </div>
                    <p class="mt-2 font-mono text-2xl font-extrabold text-[var(--app-text)]">{{ $todayAttendance?->check_in ?: '—:—:—' }}</p>
                    <p class="mt-1 text-xs text-[var(--app-muted)]">{{ $todayAttendance?->check_in ? 'Đã ghi nhận thành công' : 'Chưa thực hiện' }}</p>
                </div>

                <!-- Check-out info -->
                <div class="rounded-2xl border border-[var(--app-border)] bg-slate-50/70 dark:bg-slate-800/40 p-4">
                    <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-[var(--app-muted)]">
                        <svg class="h-4 w-4 text-sky-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" x2="9" y1="12" y2="12"/></svg>
                        <span>Giờ điểm danh ra</span>
                    </div>
                    <p class="mt-2 font-mono text-2xl font-extrabold text-[var(--app-text)]">{{ $todayAttendance?->check_out ?: '—:—:—' }}</p>
                    <p class="mt-1 text-xs text-[var(--app-muted)]">{{ $todayAttendance?->check_out ? 'Đã ghi nhận kết thúc ca' : 'Chưa thực hiện' }}</p>
                </div>

                <!-- Action Button -->
                <div class="flex flex-col justify-center rounded-2xl border border-[var(--app-border)] bg-slate-50/40 dark:bg-slate-800/20 p-4 sm:items-end">
                    @if (! $todayAttendance)
                        <button type="button" @click="open('check-in')" class="app-button-primary w-full py-3 sm:w-auto">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14.5 4h-5L7 7H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-3l-2.5-3z"/><circle cx="12" cy="13" r="3"/></svg>
                            <span>Chấm công vào ca</span>
                        </button>
                    @elseif (! $todayAttendance->check_out)
                        <button type="button" @click="open('check-out')" class="app-button-primary w-full py-3 sm:w-auto bg-gradient-to-r from-sky-600 to-indigo-600">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14.5 4h-5L7 7H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-3l-2.5-3z"/><circle cx="12" cy="13" r="3"/></svg>
                            <span>Chấm công ra ca</span>
                        </button>
                    @else
                        <div class="flex items-center gap-2 text-emerald-600 dark:text-emerald-400 font-bold text-sm">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                            <span>Ca làm việc đã hoàn tất</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Camera / Photo Modal Dialog -->
        <div x-show="openModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 p-4 backdrop-blur-md" role="dialog" aria-modal="true" aria-labelledby="attendance-modal-title">
            <div @click.outside="close()" class="w-full max-w-lg rounded-3xl border border-[var(--app-border)] bg-[var(--app-surface)] p-6 shadow-2xl">
                <div class="flex items-start justify-between gap-4 border-b border-[var(--app-border)] pb-4">
                    <div>
                        <h2 id="attendance-modal-title" class="text-lg font-bold text-[var(--app-text)]" x-text="action === 'check-in' ? 'Chấm công Vào Ca' : 'Chấm công Ra Ca'"></h2>
                        <p class="mt-1 text-xs text-[var(--app-muted)]">Chụp ảnh khuôn mặt để xác thực chấm công thông minh.</p>
                    </div>
                    <button type="button" @click="close()" class="flex h-8 w-8 items-center justify-center rounded-full text-slate-400 hover:bg-slate-100 hover:text-[var(--app-text)] dark:hover:bg-slate-800" aria-label="Đóng">&times;</button>
                </div>

                <div class="mt-5 grid gap-3 sm:grid-cols-2">
                    <button type="button" @click="startCamera()" class="app-button-secondary py-3">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14.5 4h-5L7 7H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-3l-2.5-3z"/><circle cx="12" cy="13" r="3"/></svg>
                        <span>Mở Camera</span>
                    </button>
                    <label class="app-button-secondary cursor-pointer py-3 text-center">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" x2="12" y1="3" y2="15"/></svg>
                        <span>Tải ảnh từ máy</span>
                        <input type="file" accept="image/jpeg,image/png,image/webp" @change="chooseUpload($event)" class="sr-only">
                    </label>
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

                <!-- Preview Captured / Uploaded Photo -->
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
@endsection

@push('scripts')
<script>
function selfAttendance() {
    return {
        openModal: false, action: '', previewUrl: '', photo: null, stream: null, cameraActive: false, cameraError: '', submitting: false, clock: '--:--:--',
        init() { this.tick(); setInterval(() => this.tick(), 1000); },
        tick() { this.clock = new Date().toLocaleTimeString('vi-VN'); },
        open(action) { this.action = action; this.openModal = true; this.resetPhoto(); },
        close() { this.stopCamera(); this.openModal = false; this.resetPhoto(); },
        async startCamera() {
            this.resetPhoto();
            this.cameraError = '';
            if (!navigator.mediaDevices?.getUserMedia) {
                this.cameraError = 'Không thể truy cập camera. Bạn có thể tải ảnh từ thiết bị.';
                return;
            }
            try {
                this.stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user' }, audio: false });
                this.$refs.video.srcObject = this.stream;
                this.cameraActive = true;
            } catch (error) {
                this.cameraError = 'Không thể truy cập camera. Vui lòng cấp quyền truy cập hoặc tải ảnh từ thiết bị.';
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
        chooseUpload(event) {
            this.resetPhoto();
            this.photo = event.target.files[0] || null;
            if (this.photo) this.previewUrl = URL.createObjectURL(this.photo);
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
            const form = new FormData();
            form.append('photo', this.photo);
            form.append('method', this.cameraActive ? 'camera' : (this.photo.name === 'camera-capture.jpg' ? 'camera' : 'upload'));
            const response = await fetch(this.action === 'check-in' ? '{{ route('me.attendance.check-in') }}' : '{{ route('me.attendance.check-out') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'text/html'
                },
                body: form
            });
            this.stopCamera();
            if (response.redirected) window.location.href = response.url;
            else {
                this.submitting = false;
                this.cameraError = 'Không thể lưu chấm công. Vui lòng thử lại.';
            }
        }
    };
}
</script>
@endpush
