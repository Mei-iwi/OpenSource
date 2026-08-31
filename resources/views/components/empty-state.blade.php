@props(['title' => 'Chưa có dữ liệu', 'description' => 'Dữ liệu sẽ xuất hiện tại đây sau khi chức năng được kết nối.'])
<div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 px-6 py-12 text-center">
    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-white text-xl text-slate-400 shadow-sm">＋</div>
    <h3 class="mt-4 text-sm font-semibold text-slate-800">{{ $title }}</h3>
    <p class="mx-auto mt-1 max-w-md text-sm text-slate-500">{{ $description }}</p>
</div>
