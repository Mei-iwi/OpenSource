@props(['name' => 'avatar', 'id' => null, 'src' => null, 'label' => 'Ảnh đại diện', 'maxMb' => 2])
@php($inputId = $id ?? $name)
<div class="image-picker flex flex-col items-center gap-3" x-data="imagePicker" data-initial-src="{{ $src ?? '' }}" data-max-bytes="{{ $maxMb * 1024 * 1024 }}">
    <span class="app-label">{{ $label }}</span>
    <label for="{{ $inputId }}" class="relative block h-28 w-28 cursor-pointer rounded-full border-2 border-dashed border-blue-400 bg-blue-50 shadow-sm transition hover:border-blue-600 focus-within:ring-4 focus-within:ring-blue-300 dark:bg-blue-950/40">
        <img x-show="preview" :src="preview || null" alt="Ảnh xem trước" class="h-full w-full rounded-full object-cover" @if($src) src="{{ $src }}" @else x-cloak @endif>
        <span x-show="!preview" class="absolute inset-0 flex items-center justify-center text-blue-600 dark:text-blue-300" aria-hidden="true">
            <svg class="h-9 w-9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M8 5 6 8H3v12h18V8h-3l-2-3Z"/><circle cx="12" cy="13" r="4"/></svg>
        </span>
        <span class="absolute -bottom-1 -right-1 flex h-9 w-9 items-center justify-center rounded-full border-4 border-[var(--app-surface)] bg-blue-600 text-white" aria-hidden="true">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 5 6 8H3v12h18V8h-3l-2-3Z"/><circle cx="12" cy="13" r="4"/></svg>
        </span>
        <input id="{{ $inputId }}" name="{{ $name }}" type="file" accept="image/jpeg,image/png,image/webp" class="sr-only" x-ref="input" @change="choose($event)" aria-label="Chọn {{ mb_strtolower($label) }}">
    </label>
    <p class="text-center text-xs text-[var(--app-muted)]">Chạm vào camera để chọn ảnh · JPG, PNG, WebP · tối đa {{ $maxMb }} MB</p>
    <p x-show="filename" x-text="filename" x-cloak class="max-w-full break-all text-xs text-[var(--app-muted)]"></p>
    <button type="button" x-show="filename" x-cloak @click="reset()" class="text-xs font-semibold text-blue-600 dark:text-blue-300">Bỏ ảnh vừa chọn</button>
    <p x-show="error" x-text="error" x-cloak role="alert" class="text-sm text-rose-600 dark:text-rose-300"></p>
    <x-input-error :messages="$errors->get($name)" />
</div>
