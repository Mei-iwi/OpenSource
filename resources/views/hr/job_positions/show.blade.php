@extends('layouts.app')
@section('title', $jobPosition->name)
@section('content')
<x-page-header eyebrow="HR / Danh mục" title="{{ $jobPosition->name }}" description="Chi tiết chức vụ và danh sách nhân sự hiện tại.">
    <div class="flex gap-2">
        <a href="{{ route('hr.job-positions.edit', $jobPosition) }}" class="rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">Chỉnh sửa</a>
        <a href="{{ route('hr.job-positions.index') }}" class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Quay lại danh sách</a>
    </div>
</x-page-header>
<div class="space-y-6">
    <div class="max-w-2xl rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <dl class="grid gap-5 sm:grid-cols-2">
            <div>
                <dt class="text-xs text-slate-500">Tên chức vụ</dt>
                <dd class="mt-1 font-semibold text-slate-900">{{ $jobPosition->name }}</dd>
            </div>
            <div>
                <dt class="text-xs text-slate-500">Số nhân viên đảm nhiệm</dt>
                <dd class="mt-1 font-semibold text-indigo-600">{{ $jobPosition->employees_count }} nhân viên</dd>
            </div>
        </dl>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h3 class="mb-4 text-base font-bold text-slate-900">Danh sách nhân viên ({{ $jobPosition->employees_count }})</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead class="border-b border-slate-200 text-xs uppercase text-slate-500">
                    <tr>
                        <th class="px-3 py-3">Mã NV</th>
                        <th class="px-3 py-3">Họ và tên</th>
                        <th class="px-3 py-3">Email</th>
                        <th class="px-3 py-3">Phòng ban</th>
                        <th class="px-3 py-3">Trạng thái</th>
                        <th class="px-3 py-3 text-right">Hồ sơ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($employees as $employee)
                        <tr>
                            <td class="px-3 py-4 font-mono text-xs font-semibold text-indigo-700">{{ $employee->employee_code }}</td>
                            <td class="px-3 py-4 font-semibold text-slate-900">{{ $employee->user->name }}</td>
                            <td class="px-3 py-4 text-slate-600">{{ $employee->user->email }}</td>
                            <td class="px-3 py-4 text-slate-600">{{ $employee->department?->name ?? '—' }}</td>
                            <td class="px-3 py-4">
                                <x-status-badge :status="$employee->employment_status" :label="$employee->employment_status === 'active' ? 'Đang làm' : 'Đã nghỉ'" />
                            </td>
                            <td class="px-3 py-4 text-right">
                                <a href="{{ route('hr.employees.show', $employee) }}" class="font-semibold text-indigo-600 hover:text-indigo-900">Xem hồ sơ</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-3 py-4 text-center text-slate-500">
                                Chưa có nhân viên nào đang giữ chức vụ này.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-6">{{ $employees->links() }}</div>
    </div>
</div>
@endsection
