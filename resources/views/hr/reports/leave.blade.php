@extends('layouts.app')
@section('title', 'Tổng hợp nghỉ phép')
@section('content')
<x-page-header eyebrow="HR / Báo cáo" title="Tổng hợp nghỉ phép" description="Đơn nghỉ giao với khoảng ngày đã chọn." />
@include('hr.reports._navigation')
<div class="app-panel p-6">
    <form method="GET" action="{{ route('hr.reports.leave') }}" class="grid gap-3 md:grid-cols-3 xl:grid-cols-6">
        <div><label for="leave_start" class="app-label">Từ ngày</label><input id="leave_start" name="start_date" type="date" value="{{ $filters['start_date'] ?? '' }}" class="app-input w-full"><x-input-error :messages="$errors->get('start_date')" /></div>
        <div><label for="leave_end" class="app-label">Đến ngày</label><input id="leave_end" name="end_date" type="date" value="{{ $filters['end_date'] ?? '' }}" class="app-input w-full"><x-input-error :messages="$errors->get('end_date')" /></div>
        <div><label for="leave_employee" class="app-label">Mã nhân viên</label><input id="leave_employee" name="employee_code" value="{{ $filters['employee_code'] ?? '' }}" class="app-input w-full"></div>
        <div><label for="leave_department" class="app-label">Mã phòng ban</label><input id="leave_department" name="department_code" value="{{ $filters['department_code'] ?? '' }}" class="app-input w-full"></div>
        <div><label for="leave_type" class="app-label">Loại nghỉ</label><select id="leave_type" name="leave_type" class="app-input w-full"><option value="">Tất cả</option>@foreach(['annual' => 'Phép năm', 'sick' => 'Nghỉ ốm', 'unpaid' => 'Không lương', 'other' => 'Khác'] as $value => $label)<option value="{{ $value }}" @selected(($filters['leave_type'] ?? '') === $value)>{{ $label }}</option>@endforeach</select></div>
        <div><label for="leave_status" class="app-label">Trạng thái</label><select id="leave_status" name="status" class="app-input w-full"><option value="">Tất cả</option>@foreach(['pending' => 'Chờ duyệt', 'approved' => 'Đã duyệt', 'rejected' => 'Từ chối', 'cancelled' => 'Đã hủy'] as $value => $label)<option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>@endforeach</select></div>
        <div class="flex items-end gap-2"><button class="app-button-primary">Lọc</button><a href="{{ route('hr.reports.leave') }}" class="app-button-secondary">Xóa lọc</a></div>
    </form>
    <div class="mt-5"><a href="{{ route('hr.reports.leave.csv', request()->query()) }}" class="app-button-secondary">Xuất CSV</a></div>
    <div class="mt-5 overflow-x-auto"><table class="w-full text-left text-sm"><thead><tr class="border-b"><th class="py-2">Nhân viên</th><th>Phòng ban</th><th>Loại nghỉ</th><th>Trạng thái</th><th>Từ ngày</th><th>Đến ngày</th></tr></thead><tbody>
        @forelse($rows as $row)
            <tr class="border-b"><td class="py-3">{{ $row->employee->employee_code }} · {{ $row->employee->user->name }}</td><td>{{ $row->employee->department->name }}</td><td>{{ $row->leave_type }}</td><td>{{ $row->status }}</td><td>{{ $row->start_date->format('d/m/Y') }}</td><td>{{ $row->end_date->format('d/m/Y') }}</td></tr>
        @empty
            <tr><td colspan="6" class="py-5 text-center text-[var(--app-muted)]">Không có dữ liệu phù hợp.</td></tr>
        @endforelse
    </tbody></table></div>
    <div class="mt-4">{{ $rows->links() }}</div>
</div>
@endsection
