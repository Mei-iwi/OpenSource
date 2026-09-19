<?php

namespace App\Http\Controllers\Auth;

use App\Actions\SendPasswordResetLink;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PasswordController extends Controller
{
    public function update(Request $request, SendPasswordResetLink $send): RedirectResponse
    {
        $request->validateWithBag('updatePassword', ['current_password' => ['required', 'current_password']]);

        return back()->with('success', $send($request->user()->email));
    }
}
