<section class="app-panel mt-6 p-6">
    <h2 class="font-semibold">Hợp đồng và thông tin pháp lý</h2>
    <dl class="mt-4 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
        @foreach([
            'Loại hợp đồng' => ['probation' => 'Thử việc', 'fixed-term' => 'Có thời hạn', 'indefinite' => 'Không xác định thời hạn'][$employee->contract_type] ?? null,
            'Ngày bắt đầu hợp đồng' => $employee->contract_start_date?->format('d/m/Y'),
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
