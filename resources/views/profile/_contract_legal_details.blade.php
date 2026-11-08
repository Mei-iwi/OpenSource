@if($employee)
    <section class="mt-8 border-t border-[var(--app-border)] pt-6">
        <h3 class="font-semibold">Hợp đồng và thông tin pháp lý</h3>
        <p class="app-subtitle">Thông tin hợp đồng và pháp lý do HR quản lý.</p>
        <dl class="mt-4 grid gap-4 sm:grid-cols-2">
            @foreach([
                'Loại hợp đồng' => ['probation' => 'Thử việc', 'fixed-term' => 'Có thời hạn', 'indefinite' => 'Không xác định thời hạn'][$employee->contract_type] ?? null,
                'Ngày bắt đầu hợp đồng' => $employee->contract_start_date?->format('d/m/Y'),
                'Ngày hết hạn hợp đồng' => $employee->contract_end_date?->format('d/m/Y'),
                'CCCD / CMND' => $employee->identity_number,
                'Mã số thuế' => $employee->tax_code,
                'Số BHXH' => $employee->social_insurance_number,
            ] as $label => $value)
                <div><dt class="app-label">{{ $label }}</dt><dd class="break-words text-sm">{{ $value ?: 'Chưa cập nhật' }}</dd></div>
            @endforeach
        </dl>
    </section>
@endif
