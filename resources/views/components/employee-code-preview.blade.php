<div>
    <label for="employee_code_preview" class="app-label">Mã nhân viên tự động</label>
    <output id="employee_code_preview" data-employee-code-preview data-url="{{ route('hr.employees.code-preview') }}" class="app-input block w-full" aria-live="polite">Chọn vai trò để xem mã</output>
    <p class="app-subtitle">Mã dự kiến theo vai trò. Mã chính thức được cấp khi lưu và không đổi khi chuyển vai trò.</p>
    <x-input-error :messages="$errors->get('employee_code')" />
</div>
