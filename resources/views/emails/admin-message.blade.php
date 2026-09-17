<!doctype html>
<html lang="vi">
<head><meta charset="utf-8"><title>{{ $subjectLine }}</title></head>
<body style="margin:0;background:#f8fafc;color:#0f172a;font-family:Arial,sans-serif;line-height:1.6">
    <div style="max-width:640px;margin:32px auto;padding:28px;background:#fff;border:1px solid #e2e8f0;border-radius:16px">
        <p style="margin:0 0 20px;color:#4f46e5;font-size:12px;font-weight:700;letter-spacing:.12em;text-transform:uppercase">Snake Motion</p>
        <h1 style="margin:0 0 18px;font-size:24px">{{ $subjectLine }}</h1>
        <div style="white-space:normal">{!! nl2br(e($bodyText)) !!}</div>
        <p style="margin:28px 0 0;color:#64748b;font-size:13px">Gửi bởi {{ $sender->name }} · Quản trị viên</p>
    </div>
</body>
</html>
