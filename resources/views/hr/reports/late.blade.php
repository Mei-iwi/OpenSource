@extends('layouts.app')
@section('title', 'Thống kê đi muộn')
@section('content')
<x-page-header eyebrow="HR / Báo cáo" title="Thống kê đi muộn" description="Đếm bản ghi chấm công có trạng thái đi muộn theo nhân viên." />
@include('hr.reports._navigation')
<div class="app-panel p-6">
    <form method="GET" action="{{ route('hr.reports.late') }}" class="grid gap-3 md:grid-cols-3 xl:grid-cols-5">
        <div><label for="late_month" class="app-label">Tháng</label><input id="late_month" name="month" type="number" min="1" max="12" value="{{ $filters['month'] ?? '' }}" class="app-input w-full"><x-input-error :messages="$errors->get('month')" /></div>
        <div><label for="late_year" class="app-label">Năm</label><input id="late_year" name="year" type="number" min="2000" max="2100" value="{{ $filters['year'] ?? '' }}" class="app-input w-full"><x-input-error :messages="$errors->get('year')" /></div>
        <div><label for="late_employee" class="app-label">Mã nhân viên</label><input id="late_employee" name="employee_code" value="{{ $filters['employee_code'] ?? '' }}" class="app-input w-full"></div>
        <div><label for="late_department" class="app-label">Mã phòng ban</label><input id="late_department" name="department_code" value="{{ $filters['department_code'] ?? '' }}" class="app-input w-full"></div>
        <div class="flex items-end gap-2"><button class="app-button-primary">Lọc</button><a href="{{ route('hr.reports.late') }}" class="app-button-secondary">Xóa lọc</a></div>
    </form>
    <p class="app-subtitle mt-4">Kỳ: {{ \Illuminate\Support\Carbon::parse($start)->format('d/m/Y') }} – {{ \Illuminate\Support\Carbon::parse($end)->format('d/m/Y') }}. Chưa tính “về sớm” vì hệ thống chưa có giờ kết thúc ca chuẩn.</p>
    <div class="mt-5"><a href="{{ route('hr.reports.late.csv', request()->query()) }}" class="app-button-secondary">Xuất CSV</a></div>
    <div class="mt-5 overflow-x-auto"><table class="w-full text-left text-sm"><thead><tr class="border-b"><th class="py-2">Mã nhân viên</th><th>Nhân viên</th><th>Phòng ban</th><th>Số lần đi muộn</th></tr></thead><tbody>
        @forelse($rows as $row)
            <tr class="border-b"><td class="py-3">{{ $row->employee_code }}</td><td>{{ $row->user->name }}</td><td>{{ $row->department->name }}</td><td>{{ $row->late_count }}</td></tr>
        @empty
            <tr><td colspan="4" class="py-5 text-center text-[var(--app-muted)]">Không có dữ liệu phù hợp.</td></tr>
        @endforelse
    </tbody></table></div>
    <div class="mt-4">{{ $rows->links() }}</div>
</div>
@endsection
