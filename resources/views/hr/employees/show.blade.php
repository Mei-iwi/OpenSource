@extends('layouts.app')
@section('title', $employee->user->name)
@section('content')
<x-page-header eyebrow="HR / Nhân sự" title="{{ $employee->user->name }}" description="Hồ sơ nhân viên và thông tin tài khoản.">
    <a href="{{ route('hr.employees.edit', $employee) }}" class="app-button-primary">Chỉnh sửa</a>
    <a href="{{ route('hr.contracts.show', $employee) }}" class="app-button-secondary">Xem hợp đồng</a>
</x-page-header>

@if(session('created_credentials'))
    @php($credentials = session('created_credentials'))
    <section class="mb-6 rounded-2xl border border-emerald-300 bg-emerald-50 p-5 text-emerald-950 shadow-sm dark:border-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-50" role="status">
        <h2 class="font-bold">Tạo nhân viên và tài khoản thành công</h2>
        <p class="mt-1 text-sm">Thông tin đăng nhập ban đầu được tạo từ ngày sinh. Hãy bàn giao cho nhân viên và yêu cầu đổi mật khẩu sau lần đăng nhập đầu tiên.</p>
        <dl class="mt-4 grid gap-4 sm:grid-cols-3">
            <div><dt class="text-xs opacity-70">Mã nhân viên</dt><dd class="mt-1 font-mono font-bold">{{ $credentials['employee_code'] }}</dd></div>
            <div><dt class="text-xs opacity-70">Email đăng nhập</dt><dd class="mt-1 break-all font-medium">{{ $credentials['email'] }}</dd></div>
            <div><dt class="text-xs opacity-70">Mật khẩu ban đầu</dt><dd class="mt-1 font-mono font-bold">{{ $credentials['initial_password'] }}</dd></div>
        </dl>
    </section>
@endif

<div class="app-panel p-6">
    <div class="mb-6 flex items-center gap-4 border-b border-[var(--app-border)] pb-6">
        <x-user-avatar :user="$employee->user" class="h-20 w-20 rounded-2xl" />
        <div><p class="font-semibold">{{ $employee->user->name }}</p><p class="text-sm text-[var(--app-muted)]">{{ $employee->employee_code }}</p></div>
    </div>
    <dl class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
        <div><dt class="app-label">Email</dt><dd>{{ $employee->user->email }}</dd></div>
        <div><dt class="app-label">Vai trò</dt><dd>{{ $employee->user->role === 'hr' ? 'Nhân sự' : 'Nhân viên' }}</dd></div>
        <div><dt class="app-label">Ngày sinh</dt><dd>{{ $employee->date_of_birth?->format('d/m/Y') ?: '—' }}</dd></div>
        <div><dt class="app-label">Phòng ban</dt><dd>{{ $employee->department->name }}</dd></div>
        <div><dt class="app-label">Chức vụ</dt><dd>{{ $employee->position ?: '—' }}</dd></div>
        <div><dt class="app-label">Ngày vào làm</dt><dd>{{ $employee->hire_date?->format('d/m/Y') }}</dd></div>
        <div><dt class="app-label">Trạng thái</dt><dd>{{ $employee->employment_status }}</dd></div>
        <div><dt class="app-label">Ngày ngừng làm việc</dt><dd>{{ $employee->employment_end_date?->format('d/m/Y') ?: '—' }}</dd></div>
        <div><dt class="app-label">Chấm công</dt><dd>{{ $employee->attendances_count }} bản ghi</dd></div>
    </dl>
</div>
@if(!auth()->user()->isHr() || !$employee->user->isAdmin())
<section class="app-panel mt-6 p-6">
    <div class="mb-4">
        <h2 class="app-heading">Lương nhân viên</h2>
        <p class="app-subtitle">Mức lương này áp dụng khi tạo hoặc tạo lại kỳ lương nháp. Kỳ đã chốt giữ nguyên số liệu.</p>
    </div>
    <form method="POST" action="{{ route('hr.payroll.salaries.update', $employee) }}" class="grid gap-4 md:grid-cols-3">
        @csrf @method('PUT')
        <input type="hidden" name="return_to" value="employee">
        @foreach(['base_salary' => 'Lương cơ bản tháng', 'allowance' => 'Phụ cấp tháng', 'fixed_deduction' => 'Khấu trừ cố định'] as $field => $label)
            <div>
                <label for="salary-{{ $field }}" class="app-label">{{ $label }} ({{ config('payroll.currency') }})</label>
                <input id="salary-{{ $field }}" name="{{ $field }}" inputmode="decimal" required class="app-input mt-2 w-full" value="{{ old($field, $employee->salary?->$field ?? '0.00') }}">
                <x-input-error :messages="$errors->get($field)" />
            </div>
        @endforeach
        <div class="md:col-span-3"><button class="app-button-primary">Lưu lương</button></div>
    </form>
</section>
@endif
<section class="app-panel mt-6 p-6" x-data="faceEnrollment('{{ route('hr.employees.face-reference.store', $employee) }}')" x-init="$watch('open', value => { if (!value) stopCamera() })">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h2 class="app-heading">Khuôn mặt mẫu để chấm công</h2>
            <p class="app-subtitle">{{ $employee->face_reference_path ? 'Đã đăng ký. Có thể chụp lại để thay thế.' : 'Chưa đăng ký. Nhân viên chưa thể tự chấm công.' }}</p>
        </div>
        <button type="button" class="app-button-primary" @click="openCamera()">{{ $employee->face_reference_path ? 'Chụp lại khuôn mặt' : 'Đăng ký từ camera' }}</button>
    </div>

    <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 p-4 backdrop-blur-md" role="dialog" aria-modal="true" aria-labelledby="face-enrollment-title" @keydown.escape.window="close()">
        <div class="w-full max-w-lg rounded-3xl border border-[var(--app-border)] bg-[var(--app-surface)] p-6 shadow-2xl" @click.outside="close()">
            <div class="flex items-start justify-between gap-4">
                <div><h3 id="face-enrollment-title" class="text-lg font-bold">Đăng ký khuôn mặt</h3><p class="app-subtitle">Đưa một khuôn mặt vào khung hình, đủ sáng và nhìn thẳng camera.</p></div>
                <button type="button" class="text-2xl leading-none text-[var(--app-muted)]" aria-label="Đóng" @click="close()">&times;</button>
            </div>

            <p x-show="error" x-text="error" class="mt-4 text-sm text-rose-600" role="alert"></p>
            <div x-show="cameraActive" class="mt-5 overflow-hidden rounded-2xl bg-slate-950">
                <video x-ref="video" autoplay muted playsinline class="max-h-80 w-full object-cover"></video>
            </div>
            <div x-show="previewUrl" class="mt-5 overflow-hidden rounded-2xl bg-slate-950">
                <img :src="previewUrl" alt="Ảnh khuôn mặt vừa chụp" class="max-h-80 w-full object-contain">
            </div>
            <div class="mt-5 flex flex-wrap justify-end gap-3">
                <button type="button" class="app-button-secondary" @click="close()">Hủy</button>
                <button x-show="cameraActive" type="button" class="app-button-primary" @click="capture()">Chụp khuôn mặt</button>
                <button x-show="previewUrl" type="button" class="app-button-secondary" @click="openCamera()">Chụp lại</button>
                <button x-show="previewUrl" type="button" class="app-button-primary" :disabled="saving" @click="save()" x-text="saving ? 'Đang xác thực...' : 'Lưu khuôn mặt'"></button>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
function faceEnrollment(url) {
    return {
        open: false, cameraActive: false, stream: null, photo: null, previewUrl: '', error: '', saving: false,
        async openCamera() {
            this.open = true;
            this.clearPhoto();
            this.error = '';
            this.stopCamera();
            if (!navigator.mediaDevices?.getUserMedia) {
                this.error = 'Camera chỉ hoạt động trên HTTPS hoặc localhost và cần trình duyệt hỗ trợ.';
                return;
            }
            try {
                this.stream = await navigator.mediaDevices.getUserMedia({video: {facingMode: 'user'}, audio: false});
                if (!this.open) { this.stopCamera(); return; }
                this.$refs.video.srcObject = this.stream;
                await this.$refs.video.play();
                this.cameraActive = true;
            } catch (error) {
                this.stopCamera();
                this.error = 'Không mở được camera. Hãy cấp quyền truy cập và thử lại.';
            }
        },
        capture() {
            const video = this.$refs.video;
            if (!video.videoWidth || !video.videoHeight) {
                this.error = 'Camera chưa sẵn sàng. Vui lòng thử lại.';
                return;
            }
            const canvas = document.createElement('canvas');
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            canvas.getContext('2d').drawImage(video, 0, 0);
            canvas.toBlob(blob => {
                if (!this.open) return;
                if (!blob) { this.error = 'Không chụp được ảnh. Vui lòng thử lại.'; return; }
                this.photo = new File([blob], 'face-camera.jpg', {type: 'image/jpeg'});
                this.previewUrl = URL.createObjectURL(blob);
                this.stopCamera();
            }, 'image/jpeg', 0.9);
        },
        async save() {
            if (!this.photo || this.saving) return;
            this.saving = true;
            this.error = '';
            const form = new FormData();
            form.append('face_reference', this.photo);
            try {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json'},
                    body: form,
                });
                if (!response.ok) {
                    const data = await response.json();
                    throw new Error(data.errors?.face_reference?.[0] || data.errors?.photo?.[0] || data.message || 'Không thể đăng ký khuôn mặt.');
                }
                window.location.reload();
            } catch (error) {
                this.error = error.message || 'Không thể đăng ký khuôn mặt. Vui lòng thử lại.';
                this.saving = false;
            }
        },
        clearPhoto() {
            if (this.previewUrl) URL.revokeObjectURL(this.previewUrl);
            this.previewUrl = '';
            this.photo = null;
        },
        stopCamera() {
            if (this.stream) this.stream.getTracks().forEach(track => track.stop());
            this.stream = null;
            this.cameraActive = false;
        },
        close() {
            this.open = false;
            this.stopCamera();
            this.clearPhoto();
            this.error = '';
        },
    };
}
</script>
@endpush
