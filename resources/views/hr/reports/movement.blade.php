@extends('layouts.app')
@section('title', 'Biến động nhân sự')
@section('content')
<x-page-header eyebrow="HR / Báo cáo" title="Biến động nhân sự" description="Nhân viên mới theo ngày vào làm và nhân viên ngừng làm việc theo ngày ghi nhận." />
@include('hr.reports._navigation')
<div class="app-panel p-6">
    <form method="GET" action="{{ route('hr.reports.movement') }}" class="grid gap-3 md:grid-cols-3 xl:grid-cols-6">
        <div><label for="movement_month" class="app-label">Tháng</label><input id="movement_month" name="month" type="number" min="1" max="12" value="{{ $filters['month'] ?? '' }}" class="app-input w-full"><x-input-error :messages="$errors->get('month')" /></div>
        <div><label for="movement_year" class="app-label">Năm</label><input id="movement_year" name="year" type="number" min="2000" max="2100" value="{{ $filters['year'] ?? '' }}" class="app-input w-full"><x-input-error :messages="$errors->get('year')" /></div>
        <div><label for="movement_type" class="app-label">Loại</label><select id="movement_type" name="movement_type" class="app-input w-full"><option value="">Tất cả</option><option value="new_hire" @selected(($filters['movement_type'] ?? '') === 'new_hire')>Nhân viên mới</option><option value="inactive" @selected(($filters['movement_type'] ?? '') === 'inactive')>Ngừng làm việc</option></select></div>
        <div><label for="movement_employee" class="app-label">Mã nhân viên</label><input id="movement_employee" name="employee_code" value="{{ $filters['employee_code'] ?? '' }}" class="app-input w-full"></div>
        <div><label for="movement_department" class="app-label">Mã phòng ban</label><input id="movement_department" name="department_code" value="{{ $filters['department_code'] ?? '' }}" class="app-input w-full"></div>
        <div class="flex items-end gap-2"><button class="app-button-primary">Lọc</button><a href="{{ route('hr.reports.movement') }}" class="app-button-secondary">Xóa lọc</a></div>
    </form>
    <div class="mt-5"><a href="{{ route('hr.reports.movement.csv', request()->query()) }}" class="app-button-secondary">Xuất CSV</a></div>
    <p class="app-subtitle mt-3">Hồ sơ cũ ở trạng thái ngừng làm việc nhưng chưa có ngày ngừng làm việc sẽ không xuất hiện khi lọc theo tháng hoặc năm. Phòng ban hiển thị là phòng ban hiện tại.</p>
    <div class="mt-5 overflow-x-auto"><table class="w-full text-left text-sm"><thead><tr class="border-b"><th class="py-2">Ngày</th><th>Loại</th><th>Mã nhân viên</th><th>Nhân viên</th><th>Phòng ban</th></tr></thead><tbody>
        @forelse($rows as $row)
            <tr class="border-b"><td class="py-3">{{ $row->event_date ? \Illuminate\Support\Carbon::parse($row->event_date)->format('d/m/Y') : 'Chưa ghi nhận' }}</td><td>{{ $row->movement_type === 'new_hire' ? 'Nhân viên mới' : 'Ngừng làm việc' }}</td><td>{{ $row->employee_code }}</td><td>{{ $row->employee_name }}</td><td>{{ $row->department_name }}</td></tr>
        @empty
            <tr><td colspan="5" class="py-5 text-center text-[var(--app-muted)]">Không có dữ liệu phù hợp.</td></tr>
        @endforelse
    </tbody></table></div>
    <div class="mt-4">{{ $rows->links() }}</div>
</div>
@endsection
