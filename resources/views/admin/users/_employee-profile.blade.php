@if (!isset($user) || (!$user->is(auth()->user()) && !$user->employee))
    <fieldset class="mt-6 space-y-4 border-t border-[var(--app-border)] pt-5">
        <legend class="px-1 text-sm font-semibold">Hồ sơ nhân viên</legend>
        <p class="text-sm text-[var(--app-muted)]">{{ isset($user) ? 'Tài khoản này chưa có hồ sơ nhân viên. Bổ sung thông tin để liên kết vào danh sách Nhân viên.' : 'Hồ sơ sẽ được tạo cùng tài khoản và xuất hiện trong danh sách Nhân viên, áp dụng cho cả Nhân sự và Nhân viên.' }}</p>
        @if($departments->isEmpty())
            <p class="text-sm text-amber-700 dark:text-amber-300">Cần <a class="underline" href="{{ route('hr.departments.create') }}">tạo phòng ban</a> trước khi lưu hồ sơ.</p>
        @endif
        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label for="department_id" class="app-label">Phòng ban</label>
                <select id="department_id" name="department_id" required class="app-input w-full">
                    <option value="">Chọn phòng ban</option>
                    @foreach($departments as $department)
                        <option value="{{ $department->id }}" @selected((string) old('department_id') === (string) $department->id)>{{ $department->name }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('department_id')" class="mt-1" />
            </div>
            <div>
                <label for="hire_date" class="app-label">Ngày vào làm</label>
                <input id="hire_date" name="hire_date" type="date" value="{{ old('hire_date') }}" required class="app-input w-full">
                <x-input-error :messages="$errors->get('hire_date')" class="mt-1" />
            </div>
        </div>
    </fieldset>
@elseif(isset($user) && $user->employee)
    <p class="mt-5 text-sm text-[var(--app-muted)]">Hồ sơ nhân viên đã liên kết: <a href="{{ route('hr.employees.show', $user->employee) }}" class="font-semibold text-indigo-600 underline dark:text-indigo-400">{{ $user->employee->employee_code }}</a>. Chỉnh sửa thông tin công việc tại mục Nhân viên.</p>
@endif
