<section class="app-panel mt-6 max-w-3xl p-6">
    <h2 class="app-heading">Lịch sử thay đổi vai trò</h2>
    <p class="app-subtitle">Mã nhân viên giữ nguyên theo lần cấp đầu tiên.</p>
    <div class="mt-4 overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead><tr class="border-b border-[var(--app-border)]"><th class="py-3 pr-4">Thời điểm</th><th class="py-3 pr-4">Ghi chú</th><th class="py-3">Người thực hiện</th></tr></thead>
            <tbody>
                @forelse($roleChanges as $change)
                    <tr class="border-b border-[var(--app-border)]"><td class="py-3 pr-4">{{ $change->created_at->format('d/m/Y H:i') }}</td><td class="py-3 pr-4 font-medium">{{ $change->note }}</td><td class="py-3">{{ $change->actor?->name ?? 'Hệ thống / tài khoản đã xóa' }}</td></tr>
                @empty
                    <tr><td colspan="3" class="py-4 text-[var(--app-muted)]">Chưa có thay đổi vai trò được ghi nhận.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $roleChanges->links() }}</div>
</section>
