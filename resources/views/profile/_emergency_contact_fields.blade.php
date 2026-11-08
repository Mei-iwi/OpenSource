<fieldset class="space-y-4">
    <legend class="font-semibold">Liên hệ khẩn cấp</legend>
    <div><label for="emergency_contact_name" class="app-label">Người liên hệ</label><input id="emergency_contact_name" name="emergency_contact_name" class="app-input w-full" maxlength="255" value="{{ old('emergency_contact_name', $employee->emergency_contact_name) }}"><x-input-error :messages="$errors->get('emergency_contact_name')" /></div>
    <div><label for="emergency_contact_phone" class="app-label">Số điện thoại</label><input id="emergency_contact_phone" name="emergency_contact_phone" type="tel" class="app-input w-full" maxlength="50" value="{{ old('emergency_contact_phone', $employee->emergency_contact_phone) }}"><x-input-error :messages="$errors->get('emergency_contact_phone')" /></div>
    <div><label for="emergency_contact_relationship" class="app-label">Quan hệ</label><input id="emergency_contact_relationship" name="emergency_contact_relationship" class="app-input w-full" maxlength="100" value="{{ old('emergency_contact_relationship', $employee->emergency_contact_relationship) }}"><x-input-error :messages="$errors->get('emergency_contact_relationship')" /></div>
</fieldset>
