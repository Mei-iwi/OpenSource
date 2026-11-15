<section class="app-panel p-6">
    @php($contractNeedsUpdate = !$employee->contract_type || !$employee->contract_start_date || (in_array($employee->contract_type, ['probation', 'fixed-term'], true) && !$employee->contract_end_date))
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <h2 class="font-semibold">Hợp đồng và thông tin pháp lý</h2>
            <p class="app-subtitle">{{ $contractNeedsUpdate ? 'Thông tin hợp đồng chưa đầy đủ. Hãy đối chiếu hồ sơ và bổ sung.' : 'Thông tin hợp đồng đã được cập nhật.' }}</p>
        </div>
        <a href="{{ route('hr.contracts.edit', $employee) }}" class="app-button-secondary">{{ $contractNeedsUpdate ? 'Bổ sung hợp đồng' : 'Cập nhật hợp đồng' }}</a>
    </div>
    <dl class="mt-4 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
        @foreach([
            'Loại hợp đồng' => ['probation' => 'Thử việc', 'fixed-term' => 'Có thời hạn', 'indefinite' => 'Không xác định thời hạn'][$employee->contract_type] ?? null,
            'Ngày bắt đầu hợp đồng' => $employee->contract_start_date?->format('d/m/Y'),
            'Ngày kết thúc hợp đồng' => $employee->contract_end_date?->format('d/m/Y'),
            'CCCD / CMND' => $employee->identity_number,
            'Mã số thuế' => $employee->tax_code,
            'Số BHXH' => $employee->social_insurance_number,
            'Người liên hệ khẩn cấp' => $employee->emergency_contact_name,
            'Số điện thoại khẩn cấp' => $employee->emergency_contact_phone,
            'Quan hệ' => $employee->emergency_contact_relationship,
        ] as $label => $value)
            <div><dt class="app-label">{{ $label }}</dt><dd class="break-words">{{ $value ?: '—' }}</dd></div>
        @endforeach
    </dl>
</section>
