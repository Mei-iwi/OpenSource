@csrf
<div class="space-y-5">
    <div>
        <label for="name" class="text-sm font-medium text-slate-700">Tên chức vụ / vị trí</label>
        <input id="name" name="name" value="{{ old('name', $jobPosition->name ?? '') }}" required placeholder="Ví dụ: Kỹ sư phần mềm" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500">
        <x-input-error :messages="$errors->get('name')" class="mt-1" />
    </div>
</div>
<div class="mt-6 flex gap-3">
    <button class="rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">
        {{ isset($jobPosition) ? 'Lưu thay đổi' : 'Tạo chức vụ' }}
    </button>
    <a href="{{ route('hr.job-positions.index') }}" class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Hủy</a>
</div>
