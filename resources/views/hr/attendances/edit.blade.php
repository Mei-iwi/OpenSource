@extends('layouts.app')
@section('title', 'Sửa chấm công')
@section('content')
<x-page-header eyebrow="HR / Vận hành" title="Sửa chấm công" description="Cập nhật bản ghi đã chọn.">
    @can('delete', $attendance)
        <form method="POST" action="{{ route('hr.attendances.destroy', $attendance) }}" onsubmit="return confirm('Bạn có chắc chắn muốn xóa bản ghi chấm công ngày {{ $attendance->work_date ? $attendance->work_date->format('d/m/Y') : '' }} của {{ addslashes($attendance->employee->user->name ?? '') }}?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="rounded-lg bg-rose-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-rose-700">Xóa bản ghi</button>
        </form>
    @endcan
</x-page-header>
<form method="POST" action="{{ route('hr.attendances.update', $attendance) }}" class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
    @method('PUT')
    @include('hr.attendances._form')
</form>
@endsection
