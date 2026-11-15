@extends('layouts.app')
@section('title', 'Quản lý hợp đồng')
@section('content')
<x-page-header eyebrow="HR / Hợp đồng" title="Quản lý hợp đồng" description="Theo dõi và cập nhật hợp đồng của từng nhân viên." />

<section class="app-panel p-5 sm:p-6">
    <form method="GET" action="{{ route('hr.contracts.index') }}" class="grid gap-3 md:grid-cols-2 xl:grid-cols-[minmax(200px,1fr)_220px_220px_auto]">
        <input class="app-input w-full" type="search" name="search" value="{{ request('search') }}" placeholder="Tên hoặc mã nhân viên" aria-label="Tìm nhân viên">
        <select class="app-input w-full" name="department_id" aria-label="Phòng ban">
            <option value="">Tất cả phòng ban</option>
            @foreach($departments as $department)
                <option value="{{ $department->id }}" @selected((string) request('department_id') === (string) $department->id)>{{ $department->name }}</option>
            @endforeach
        </select>
        <select class="app-input w-full" name="status" aria-label="Tình trạng hợp đồng">
            <option value="">Tất cả hợp đồng</option>
            <option value="missing" @selected(request('status') === 'missing')>Cần bổ sung</option>
            <option value="expiring" @selected(request('status') === 'expiring')>Sắp hết hạn (30 ngày)</option>
            <option value="expired" @selected(request('status') === 'expired')>Đã hết hạn</option>
        </select>
        <div class="flex gap-2"><button class="app-button-primary" type="submit">Lọc</button><a class="app-button-secondary" href="{{ route('hr.contracts.index') }}">Đặt lại</a></div>
    </form>

    <div class="mt-6 overflow-x-auto">
        <table class="w-full min-w-[760px] text-left text-sm">
            <thead class="border-b border-[var(--app-border)] text-[var(--app-muted)]"><tr>
                <th class="px-3 py-3 font-semibold">Nhân viên</th>
                <th class="px-3 py-3 font-semibold">Phòng ban</th>
                <th class="px-3 py-3 font-semibold">Loại hợp đồng</th>
                <th class="px-3 py-3 font-semibold">Thời hạn</th>
                <th class="px-3 py-3 font-semibold">Tình trạng</th>
                <th class="px-3 py-3 text-right font-semibold">Thao tác</th>
            </tr></thead>
            <tbody>
                @forelse($employees as $employee)
                    @php($incomplete = !$employee->contract_type || !$employee->contract_start_date || (in_array($employee->contract_type, ['probation', 'fixed-term'], true) && !$employee->contract_end_date))
                    <tr class="border-b border-[var(--app-border)] last:border-0">
                        <td class="px-3 py-4"><span class="block font-semibold">{{ $employee->user->name }}</span><span class="text-[var(--app-muted)]">{{ $employee->employee_code }}</span></td>
                        <td class="px-3 py-4">{{ $employee->department->name }}</td>
                        <td class="px-3 py-4">{{ ['probation' => 'Thử việc', 'fixed-term' => 'Có thời hạn', 'indefinite' => 'Không xác định thời hạn'][$employee->contract_type] ?? '—' }}</td>
                        <td class="px-3 py-4">{{ $employee->contract_start_date?->format('d/m/Y') ?? '—' }} → {{ $employee->contract_type === 'indefinite' ? 'Không thời hạn' : ($employee->contract_end_date?->format('d/m/Y') ?? '—') }}</td>
                        <td class="px-3 py-4">{{ $incomplete ? 'Cần bổ sung' : ($employee->contract_end_date?->toDateString() < $today && $employee->contract_end_date ? 'Đã hết hạn' : ($employee->contract_end_date && $employee->contract_end_date->toDateString() <= $soon ? 'Sắp hết hạn' : 'Đã cập nhật')) }}</td>
                        <td class="px-3 py-4 text-right"><a class="font-semibold text-[var(--app-accent)] hover:underline" href="{{ route('hr.contracts.show', $employee) }}">Xem hợp đồng</a></td>
                    </tr>
                @empty
                    <tr><td class="px-3 py-10 text-center text-[var(--app-muted)]" colspan="6">Không có hợp đồng phù hợp với bộ lọc.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-5">{{ $employees->links() }}</div>
</section>
@endsection
