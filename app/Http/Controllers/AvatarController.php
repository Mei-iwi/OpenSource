<?php

namespace App\Http\Controllers;

use App\Models\User;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AvatarController extends Controller
{
    public function show(User $user): StreamedResponse
    {
        $path = $user->avatar_path ?: $user->employee?->avatar_path;
        abort_unless($path, 404);

        return $this->streamStoredFile(config('filesystems.avatar_disk'), $path, [
            'Cache-Control' => 'private, max-age=3600',
        ]);
    }
}
