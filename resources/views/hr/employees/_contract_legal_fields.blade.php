<fieldset class="mt-8 border-t border-[var(--app-border)] pt-6">
    <legend class="font-semibold">Hợp đồng và thông tin pháp lý</legend>
    <div class="mt-4 grid gap-5 sm:grid-cols-2">
        <div><label for="contract_type" class="app-label">Loại hợp đồng</label><select id="contract_type" name="contract_type" class="app-input w-full">
            <option value="">Chưa cập nhật</option>
            @foreach(['probation' => 'Thử việc', 'fixed-term' => 'Có thời hạn', 'indefinite' => 'Không xác định thời hạn'] as $value => $label)
                <option value="{{ $value }}" @selected(old('contract_type', $employee->contract_type ?? '') === $value)>{{ $label }}</option>
            @endforeach
        </select><x-input-error :messages="$errors->get('contract_type')" /></div>
        <div><label for="contract_start_date" class="app-label">Ngày bắt đầu hợp đồng</label><input id="contract_start_date" name="contract_start_date" type="date" class="app-input w-full" value="{{ old('contract_start_date', isset($employee) && $employee->contract_start_date ? $employee->contract_start_date->format('Y-m-d') : '') }}"><x-input-error :messages="$errors->get('contract_start_date')" /></div>
        <div><label for="identity_number" class="app-label">CCCD / CMND</label><input id="identity_number" name="identity_number" class="app-input w-full" maxlength="20" autocomplete="off" value="{{ old('identity_number', $employee->identity_number ?? '') }}"><x-input-error :messages="$errors->get('identity_number')" /></div>
        <div><label for="tax_code" class="app-label">Mã số thuế</label><input id="tax_code" name="tax_code" class="app-input w-full" maxlength="30" autocomplete="off" value="{{ old('tax_code', $employee->tax_code ?? '') }}"><x-input-error :messages="$errors->get('tax_code')" /></div>
        <div><label for="social_insurance_number" class="app-label">Số BHXH</label><input id="social_insurance_number" name="social_insurance_number" class="app-input w-full" maxlength="30" autocomplete="off" value="{{ old('social_insurance_number', $employee->social_insurance_number ?? '') }}"><x-input-error :messages="$errors->get('social_insurance_number')" /></div>
        <div><label for="emergency_contact_name" class="app-label">Người liên hệ khẩn cấp</label><input id="emergency_contact_name" name="emergency_contact_name" class="app-input w-full" maxlength="255" value="{{ old('emergency_contact_name', $employee->emergency_contact_name ?? '') }}"><x-input-error :messages="$errors->get('emergency_contact_name')" /></div>
        <div><label for="emergency_contact_phone" class="app-label">Số điện thoại khẩn cấp</label><input id="emergency_contact_phone" name="emergency_contact_phone" type="tel" class="app-input w-full" maxlength="50" value="{{ old('emergency_contact_phone', $employee->emergency_contact_phone ?? '') }}"><x-input-error :messages="$errors->get('emergency_contact_phone')" /></div>
        <div><label for="emergency_contact_relationship" class="app-label">Quan hệ</label><input id="emergency_contact_relationship" name="emergency_contact_relationship" class="app-input w-full" maxlength="100" value="{{ old('emergency_contact_relationship', $employee->emergency_contact_relationship ?? '') }}"><x-input-error :messages="$errors->get('emergency_contact_relationship')" /></div>
    </div>
</fieldset>
