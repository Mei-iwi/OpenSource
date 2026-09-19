<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Throwable;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user()->load('employee.department'),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->safe()->only(['phone', 'address']);
        $oldAvatar = $user->avatar_path;
        $newAvatar = $request->hasFile('avatar')
            ? $request->file('avatar')->store('avatars', config('filesystems.avatar_disk'))
            : $oldAvatar;

        try {
            DB::transaction(function () use ($user, $data, $newAvatar) {
                $user->update(['avatar_path' => $newAvatar]);
                $user->employee?->update($data);
            });
        } catch (Throwable $exception) {
            if ($newAvatar && $newAvatar !== $oldAvatar) {
                Storage::disk(config('filesystems.avatar_disk'))->delete($newAvatar);
            }
            throw $exception;
        }

        if ($newAvatar !== $oldAvatar && $oldAvatar) {
            Storage::disk(config('filesystems.avatar_disk'))->delete($oldAvatar);
        }

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }
}
