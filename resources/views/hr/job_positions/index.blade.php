@extends('layouts.app')
@section('title', 'Chức vụ')
@section('content')
<x-page-header eyebrow="HR / Danh mục" title="Chức vụ" description="Quản lý vị trí công việc và phân bổ nhân sự.">
    <a href="{{ route('hr.job-positions.create') }}" class="rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">+ Thêm chức vụ</a>
</x-page-header>
<div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
    <form method="GET" action="{{ route('hr.job-positions.index') }}" class="mb-6 flex flex-col gap-3 sm:flex-row">
        <label class="sr-only" for="job-position-search">Tìm chức vụ</label>
        <input id="job-position-search" name="search" value="{{ request('search') }}" placeholder="Tìm theo tên chức vụ..." class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm sm:max-w-sm">
        <button class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Tìm kiếm</button>
        <a href="{{ route('hr.job-positions.index') }}" class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-500 hover:bg-slate-50">Đặt lại</a>
    </form>
    <div class="overflow-x-auto">
        <table class="min-w-full text-left text-sm">
            <thead class="border-b border-slate-200 text-xs uppercase text-slate-500">
                <tr>
                    <th class="px-3 py-3">Tên chức vụ</th>
                    <th class="px-3 py-3">Số lượng nhân viên</th>
                    <th class="px-3 py-3 text-right">Thao tác</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($jobPositions as $jobPosition)
                    <tr>
                        <td class="px-3 py-4 font-semibold text-slate-900">{{ $jobPosition->name }}</td>
                        <td class="px-3 py-4">
                            <x-status-badge status="active" :label="$jobPosition->employees_count.' nhân viên'" />
                        </td>
                        <td class="px-3 py-4">
                            <div class="flex justify-end gap-3">
                                <a href="{{ route('hr.job-positions.show', $jobPosition) }}" class="text-sm font-semibold text-slate-600 hover:text-slate-900">Xem</a>
                                <a href="{{ route('hr.job-positions.edit', $jobPosition) }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-900">Sửa</a>
                                @if ($jobPosition->employees_count === 0)
                                    <form method="POST" action="{{ route('hr.job-positions.destroy', $jobPosition) }}" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button onclick="return confirm('Bạn có chắc chắn muốn xóa chức vụ {{ addslashes($jobPosition->name) }}?');" class="text-sm font-semibold text-rose-600 hover:text-rose-900">Xóa</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-3 py-4">
                            <x-empty-state title="Chưa có chức vụ nào" description="Tạo chức vụ đầu tiên để bắt đầu quản lý danh mục." />
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-6">{{ $jobPositions->links() }}</div>
</div>
@endsection
