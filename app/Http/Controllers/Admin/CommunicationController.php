<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\AdminMessageMail;
use App\Models\AdminMessage;
use App\Models\Advertisement;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\View\View;

class CommunicationController extends Controller
{
    public function index(): View
    {
        $recipients = User::query()
            ->whereIn('role', ['hr', 'employee'])
            ->where('account_status', 'active')
            ->orderBy('role')
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'role']);
        $advertisement = Advertisement::query()->latest('id')->first();
        $messages = AdminMessage::query()->latest()->limit(10)->get();

        return view('admin.communications.index', compact('recipients', 'advertisement', 'messages'));
    }

    public function sendMessage(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'subject' => ['required', 'string', 'max:180'],
            'body' => ['required', 'string', 'max:10000'],
            'audience' => ['required', 'in:all,hr,employee,selected'],
            'recipient_ids' => ['nullable', 'array'],
            'recipient_ids.*' => ['integer', 'exists:users,id'],
        ]);

        $query = User::query()
            ->whereIn('role', ['hr', 'employee'])
            ->where('account_status', 'active');

        if ($data['audience'] === 'hr') {
            $query->where('role', 'hr');
        } elseif ($data['audience'] === 'employee') {
            $query->where('role', 'employee');
        } elseif ($data['audience'] === 'selected') {
            $ids = collect($data['recipient_ids'] ?? [])->map(fn ($id) => (int) $id)->values();
            if ($ids->isEmpty()) {
                return back()->withErrors(['recipient_ids' => 'Hãy chọn ít nhất một người nhận.'])->withInput();
            }
            $query->whereIn('id', $ids);
        }

        $recipients = $query->get(['id', 'email']);
        if ($recipients->isEmpty()) {
            return back()->withErrors(['audience' => 'Không có tài khoản đang hoạt động phù hợp để nhận thư.'])->withInput();
        }

        $recipients->each(function (User $recipient) use ($data): void {
            Mail::to($recipient->email)->send(new AdminMessageMail(
                auth()->user(),
                $data['subject'],
                $data['body'],
            ));
        });

        AdminMessage::create([
            'admin_id' => auth()->id(),
            'subject' => $data['subject'],
            'body' => $data['body'],
            'audience' => $data['audience'],
            'recipient_user_ids' => $recipients->pluck('id')->values()->all(),
            'recipient_count' => $recipients->count(),
            'sent_at' => now(),
        ]);

        return back()->with('success', "Đã gửi thư đến {$recipients->count()} tài khoản.");
    }

    public function saveAdvertisement(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'message' => ['required', 'string', 'max:5000'],
            'image' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'display_seconds' => ['required', 'integer', 'min:5', 'max:300'],
            'repeat_seconds' => ['required', 'integer', 'min:10', 'max:3600', 'gte:display_seconds'],
            'is_active' => ['nullable', 'boolean'],
        ]);
        $data['is_active'] = $request->boolean('is_active');

        $advertisement = Advertisement::query()->latest('id')->first();
        $oldImage = $advertisement?->image_path;
        $newImage = $request->hasFile('image')
            ? $request->file('image')->store('advertisements', config('filesystems.avatar_disk'))
            : $oldImage;
        $data['image_path'] = $newImage;
        unset($data['image']);
        if ($data['is_active']) {
            Advertisement::query()->update(['is_active' => false]);
        }
        if ($advertisement) {
            $advertisement->update($data);
        } else {
            Advertisement::create($data);
        }
        if ($oldImage && $newImage !== $oldImage) {
            Storage::disk(config('filesystems.avatar_disk'))->delete($oldImage);
        }

        return back()->with('success', $data['is_active']
            ? 'Đã lưu và bật quảng cáo cho tài khoản nhân viên.'
            : 'Đã lưu quảng cáo ở trạng thái tắt.');
    }

    public function toggleAdvertisement(Advertisement $advertisement): RedirectResponse
    {
        $nextState = ! $advertisement->is_active;
        if ($nextState) {
            Advertisement::query()->where('id', '!=', $advertisement->id)->update(['is_active' => false]);
        }
        $advertisement->update(['is_active' => $nextState]);

        return back()->with('success', $nextState ? 'Đã bật quảng cáo.' : 'Đã tắt quảng cáo.');
    }

    public function activeAdvertisement(): JsonResponse
    {
        $advertisement = Advertisement::active()->latest('id')->first();

        return response()->json($advertisement ? [
            'active' => true,
            'id' => $advertisement->id,
            'title' => $advertisement->title,
            'message' => $advertisement->message,
            'image_url' => $advertisement->image_path ? route('advertisement.image', $advertisement) : null,
            'display_seconds' => $advertisement->display_seconds,
            'repeat_seconds' => $advertisement->repeat_seconds,
        ] : ['active' => false]);
    }

    public function showAdvertisementImage(Request $request, Advertisement $advertisement): StreamedResponse
    {
        $path = $advertisement->image_path;
        $disk = Storage::disk(config('filesystems.avatar_disk'));
        abort_unless($path && $disk->exists($path), 404);
        $stream = $disk->readStream($path);
        abort_unless(is_resource($stream), 404);

        return response()->stream(function () use ($stream): void {
            fpassthru($stream);
            fclose($stream);
        }, 200, [
            'Content-Type' => $disk->mimeType($path) ?: 'application/octet-stream',
            'Content-Disposition' => 'inline; filename="'.basename($path).'"',
            'Cache-Control' => 'private, max-age=3600',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
