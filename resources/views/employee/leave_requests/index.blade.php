@extends('layouts.app')
@section('title', 'Đơn nghỉ của tôi')
@section('content')
<x-page-header eyebrow="Cá nhân" title="Đơn nghỉ của tôi" description="Theo dõi các đơn xin nghỉ của bạn.">
    @if($hasEmployeeProfile ?? true)
        <a href="{{ route('employee.leave-requests.create') }}" class="app-button-primary">Gửi đơn xin nghỉ</a>
    @endif
</x-page-header>

@if(! ($hasEmployeeProfile ?? true))
    <div class="app-panel mb-6 border-l-4 border-l-amber-500 bg-amber-500/5 p-4 text-sm text-amber-800 dark:text-amber-300">
        <p class="font-bold">Tài khoản chưa liên kết hồ sơ nhân viên</p>
        <p class="mt-1 text-xs text-[var(--app-muted)]">Bạn đang đăng nhập bằng tài khoản quản trị/nhân sự chưa có hồ sơ nhân viên tương ứng. Vui lòng liên kết hồ sơ nhân viên trước khi gửi đơn nghỉ cá nhân.</p>
    </div>
@endif

<div class="app-panel overflow-hidden">
    <div class="overflow-x-auto">
        <table class="app-table">
            <thead>
                <tr>
                    <th>Loại nghỉ</th>
                    <th>Thời gian</th>
                    <th>Số ngày</th>
                    <th>Trạng thái</th>
                    <th>Ngày gửi</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($requests as $request)
                    <tr>
                        <td>{{ ['annual'=>'Nghỉ phép năm','sick'=>'Nghỉ ốm','unpaid'=>'Nghỉ không lương','other'=>'Khác'][$request->leave_type] ?? $request->leave_type }}</td>
                        <td>{{ $request->start_date->format('d/m/Y') }} - {{ $request->end_date->format('d/m/Y') }}</td>
                        <td>{{ $request->start_date->diffInDays($request->end_date) + 1 }}</td>
                        <td><x-status-badge :status="$request->status" :label="['pending'=>'Đang chờ','approved'=>'Đã duyệt','rejected'=>'Từ chối','cancelled'=>'Đã hủy'][$request->status] ?? $request->status" /></td>
                        <td>{{ $request->created_at->format('d/m/Y') }}</td>
                        <td class="text-right">
                            <a href="{{ route('employee.leave-requests.show', $request) }}" class="font-semibold text-orange-600 hover:text-orange-700">Chi tiết</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="py-12 text-center text-[var(--app-muted)]">Chưa có đơn xin nghỉ.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-5">
        {{ $requests->links() }}
    </div>
</div>
@endsection
