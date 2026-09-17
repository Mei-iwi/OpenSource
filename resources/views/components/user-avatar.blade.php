@props(['user'])

@php
    $defaultAvatar = match ($user?->role) {
        'hr' => 'images/default-hr.png',
        'employee' => 'images/default-employee.png',
        default => 'images/default-user.webp',
    };
@endphp

<span {{ $attributes->merge(['class' => 'relative inline-flex items-center justify-center overflow-hidden rounded-full bg-slate-100']) }}>
    <img
        src="{{ $user?->avatar_url ?: asset($defaultAvatar) }}"
        alt="Ảnh đại diện {{ $user?->name ?? 'người dùng' }}"
        class="h-full w-full object-cover"
        loading="lazy"
    >
</span>
