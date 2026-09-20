@extends('layouts.app')
@section('title', 'Hồ sơ cá nhân')
@section('content')
@php($employee = $user->employee)
<x-page-header eyebrow="Cá nhân" title="Hồ sơ cá nhân" description="Thông tin nhân sự và liên hệ của bạn." />
<div class="grid gap-6 xl:grid-cols-[minmax(0,2fr)_minmax(280px,1fr)]">
    <section class="app-panel p-6 sm:p-8">
        <div class="flex flex-wrap items-center gap-5 border-b border-[var(--app-border)] pb-6">
            <x-user-avatar :user="$user" class="h-24 w-24 rounded-2xl" />
            <div><p class="text-xs font-semibold uppercase tracking-widest text-[var(--app-muted)]">Hồ sơ nhân sự</p>
                <h2 class="mt-2 text-2xl font-bold">{{ $user->name }}</h2>
                <p class="mt-1 text-sm text-[var(--app-muted)]">{{ $employee?->position ?? (['admin' => 'Quản trị viên', 'hr' => 'Nhân sự', 'employee' => 'Nhân viên'][$user->role] ?? '') }}</p>
            </div>
        </div>
        <h3 class="mt-6 font-semibold">Thông tin chính thức</h3>
        <p class="app-subtitle">Thông tin do bộ phận nhân sự quản lý. Liên hệ nhân sự nếu cần điều chỉnh.</p>
        <dl class="mt-5 grid gap-x-8 gap-y-5 sm:grid-cols-2">
            @foreach([
                'Mã nhân viên' => $employee?->employee_code,
                'Email tài khoản' => $user->email,
                'Ngày sinh' => $employee?->date_of_birth?->format('d/m/Y'),
                'Phòng ban' => $employee?->department?->name,
                'Chức vụ' => $employee?->position,
                'Ngày vào làm' => $employee?->hire_date?->format('d/m/Y'),
                'Trạng thái làm việc' => (['active' => 'Đang làm việc', 'inactive' => 'Ngừng làm việc', 'resigned' => 'Đã nghỉ việc'][$employee?->employment_status] ?? $employee?->employment_status),
            ] as $label => $value)
                <div><dt class="text-xs text-[var(--app-muted)]">{{ $label }}</dt><dd class="mt-1 break-words text-sm font-medium">{{ $value ?: 'Chưa cập nhật' }}</dd></div>
            @endforeach
        </dl>
        <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="mt-8 space-y-5 border-t border-[var(--app-border)] pt-6">
            @csrf @method('PATCH')
            <h3 class="font-semibold">Ảnh và thông tin liên hệ</h3>
            <x-image-picker name="avatar" :src="$user->avatar_url" />
            @if($employee)
                <div><label for="phone" class="app-label">Số điện thoại</label><input id="phone" name="phone" type="tel" maxlength="30" autocomplete="tel" class="app-input w-full" value="{{ old('phone', $employee->phone) }}"><x-input-error :messages="$errors->get('phone')" /></div>
                <div><label for="address" class="app-label">Địa chỉ liên hệ</label><textarea id="address" name="address" maxlength="500" autocomplete="street-address" class="app-input w-full" rows="3">{{ old('address', $employee->address) }}</textarea><x-input-error :messages="$errors->get('address')" /></div>
            @endif
            <button type="submit" class="app-button-primary">Lưu thông tin</button>
            @if(session('status') === 'profile-updated')<p class="text-sm text-emerald-600 dark:text-emerald-400" role="status">Đã cập nhật hồ sơ.</p>@endif
        </form>
    </section>
    <div class="app-panel self-start p-6">@include('profile.partials.update-password-form')</div>
</div>
@endsection
