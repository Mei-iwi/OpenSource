@extends('layouts.app')
@section('title', 'Cấu hình lương')
@section('content')
<x-page-header eyebrow="HR / Lương" title="Cấu hình lương" description="Mức lương dùng cho các kỳ nháp được tạo sau khi cập nhật.">
    <a href="{{ route('hr.payroll.index') }}" class="app-button-secondary">Về bảng lương</a>
</x-page-header>
<div class="app-panel overflow-x-auto p-6">
    <table class="w-full text-left text-sm"><thead><tr class="border-b"><th class="py-2">Nhân viên</th><th>Lương cơ bản</th><th>Phụ cấp</th><th>Khấu trừ cố định</th><th></th></tr></thead><tbody>
        @foreach($employees as $employee)
            <tr class="border-b"><td class="py-3">{{ $employee->employee_code }} · {{ $employee->user->name }}</td><td>{{ $employee->salary?->base_salary ?? '—' }}</td><td>{{ $employee->salary?->allowance ?? '—' }}</td><td>{{ $employee->salary?->fixed_deduction ?? '—' }}</td><td><a href="{{ route('hr.payroll.salaries.edit', $employee) }}" class="font-semibold text-indigo-600 underline">Chỉnh sửa</a></td></tr>
        @endforeach
    </tbody></table>
    <div class="mt-4">{{ $employees->links() }}</div>
</div>
@endsection
