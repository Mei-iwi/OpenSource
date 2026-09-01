@extends('layouts.app')
@section('title', 'Chấm công của tôi')
@section('content')
<x-page-header eyebrow="Cá nhân" title="Chấm công của tôi" description="Xác nhận giờ vào và giờ ra bằng ảnh từ camera hoặc thiết bị.">
    <a href="{{ route('dashboard') }}" class="app-button-secondary">Về tổng quan</a>
</x-page-header>

@if (! $employee)
    <div class="app-panel p-6"><x-empty-state title="Chưa có hồ sơ nhân viên" description="Tài khoản chưa được gắn với hồ sơ nhân viên nên chưa thể chấm công." /></div>
@else
    <div x-data="selfAttendance()" class="space-y-6">
        <div class="app-panel flex flex-wrap items-center gap-4 p-5 sm:p-6">
            <div class="flex h-16 w-16 items-center justify-center overflow-hidden rounded-full bg-orange-100 text-xl font-bold text-orange-700">
                @if ($employee->user->avatar_path)
                    <img src="{{ $employee->user->avatar_url }}" alt="Ảnh đại diện {{ $employee->user->name }}" class="h-full w-full object-cover">
                @else
                    {{ strtoupper(substr($employee->user->name, 0, 1)) }}
                @endif
            </div>
            <div class="min-w-48 flex-1"><h2 class="app-heading">{{ $employee->user->name }}</h2><p class="app-subtitle">{{ $employee->employee_code }} · {{ $employee->department?->name ?? 'Chưa phân phòng ban' }}</p></div>
            <div class="text-left sm:text-right"><p class="text-xs font-semibold uppercase tracking-wide text-[var(--app-muted)]">Hôm nay</p><p class="mt-1 text-lg font-bold text-[var(--app-text)]">{{ now()->format('d/m/Y') }}</p><p class="text-sm text-[var(--app-muted)]" x-text="clock">--:--:--</p></div>
        </div>

        <div class="app-panel p-5 sm:p-6">
            <div class="flex flex-wrap items-start justify-between gap-4"><div><h2 class="app-heading">Trạng thái hôm nay</h2><p class="app-subtitle">Ảnh xác nhận được lưu riêng tư, không công khai.</p></div><span class="app-badge app-badge-info">{{ $todayAttendance ? ($todayAttendance->check_out ? 'Đã hoàn tất' : 'Đã vào ca') : 'Chưa chấm công' }}</span></div>
            <div class="mt-6 grid gap-4 sm:grid-cols-3">
                <div class="rounded-xl border border-[var(--app-border)] p-4"><p class="text-xs text-[var(--app-muted)]">Giờ vào</p><p class="mt-1 text-xl font-bold text-[var(--app-text)]">{{ $todayAttendance?->check_in ?: '—' }}</p></div>
                <div class="rounded-xl border border-[var(--app-border)] p-4"><p class="text-xs text-[var(--app-muted)]">Giờ ra</p><p class="mt-1 text-xl font-bold text-[var(--app-text)]">{{ $todayAttendance?->check_out ?: '—' }}</p></div>
                <div class="flex items-center sm:justify-end">
                    @if (! $todayAttendance)
                        <button type="button" @click="open('check-in')" class="app-button-primary w-full sm:w-auto">Chấm công vào</button>
                    @elseif (! $todayAttendance->check_out)
                        <button type="button" @click="open('check-out')" class="app-button-primary w-full sm:w-auto">Chấm công ra</button>
                    @else
                        <span class="text-sm font-semibold text-emerald-600">Ca làm việc đã hoàn tất</span>
                    @endif
                </div>
            </div>
        </div>

        <div x-show="openModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 p-4 backdrop-blur-sm" role="dialog" aria-modal="true" aria-labelledby="attendance-modal-title">
            <div @click.outside="close()" class="w-full max-w-lg rounded-2xl border border-[var(--app-border)] bg-[var(--app-surface)] p-6 shadow-2xl">
                <div class="flex items-start justify-between gap-4"><div><h2 id="attendance-modal-title" class="text-lg font-bold text-[var(--app-text)]" x-text="action === 'check-in' ? 'Chấm công vào' : 'Chấm công ra'"></h2><p class="mt-1 text-sm text-[var(--app-muted)]">Chọn một phương thức xác nhận cho lần chấm công này.</p></div><button type="button" @click="close()" class="text-2xl text-[var(--app-muted)]" aria-label="Đóng">&times;</button></div>
                <div class="mt-5 grid gap-3 sm:grid-cols-2"><button type="button" @click="startCamera()" class="app-button-secondary">Chụp ảnh</button><label class="app-button-secondary cursor-pointer">Tải ảnh lên<input type="file" accept="image/jpeg,image/png,image/webp" @change="chooseUpload($event)" class="sr-only"></label></div>
                <p x-show="cameraError" x-text="cameraError" class="mt-3 text-sm text-amber-600" role="alert"></p>
                <div x-show="cameraActive" class="mt-4 space-y-3"><video x-ref="video" autoplay playsinline class="max-h-64 w-full rounded-xl bg-slate-900 object-cover"></video><button type="button" @click="capture()" class="app-button-primary w-full">Chụp</button></div>
                <div x-show="previewUrl" class="mt-4 space-y-3"><img :src="previewUrl" alt="Ảnh xem trước" class="max-h-64 w-full rounded-xl object-contain"><div class="flex justify-end gap-3"><button type="button" @click="resetPhoto()" class="app-button-secondary">Chọn lại</button><button type="button" @click="submit()" :disabled="submitting" class="app-button-primary"><span x-text="submitting ? 'Đang xử lý...' : 'Xác nhận chấm công'"></span></button></div></div>
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
        async startCamera() { this.resetPhoto(); this.cameraError = ''; if (!navigator.mediaDevices?.getUserMedia) { this.cameraError = 'Không thể truy cập camera. Bạn có thể tải ảnh từ thiết bị.'; return; } try { this.stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user' }, audio: false }); this.$refs.video.srcObject = this.stream; this.cameraActive = true; } catch (error) { this.cameraError = 'Không thể truy cập camera. Bạn có thể tải ảnh từ thiết bị.'; this.stopCamera(); } },
        capture() { const video = this.$refs.video; const canvas = document.createElement('canvas'); canvas.width = video.videoWidth; canvas.height = video.videoHeight; canvas.getContext('2d').drawImage(video, 0, 0); canvas.toBlob((blob) => { this.photo = new File([blob], 'camera-capture.jpg', { type: 'image/jpeg' }); this.previewUrl = URL.createObjectURL(blob); this.stopCamera(); }, 'image/jpeg', .9); },
        chooseUpload(event) { this.resetPhoto(); this.photo = event.target.files[0] || null; if (this.photo) this.previewUrl = URL.createObjectURL(this.photo); },
        resetPhoto() { this.stopCamera(); if (this.previewUrl) URL.revokeObjectURL(this.previewUrl); this.previewUrl = ''; this.photo = null; },
        stopCamera() { if (this.stream) this.stream.getTracks().forEach(track => track.stop()); this.stream = null; this.cameraActive = false; },
        async submit() { if (!this.photo || this.submitting) return; this.submitting = true; const form = new FormData(); form.append('photo', this.photo); form.append('method', this.cameraActive ? 'camera' : (this.photo.name === 'camera-capture.jpg' ? 'camera' : 'upload')); const response = await fetch(this.action === 'check-in' ? '{{ route('me.attendance.check-in') }}' : '{{ route('me.attendance.check-out') }}', { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'text/html' }, body: form }); this.stopCamera(); if (response.redirected) window.location.href = response.url; else { this.submitting = false; this.cameraError = 'Không thể lưu chấm công. Vui lòng thử lại.'; } }
    };
}
</script>
@endpush
