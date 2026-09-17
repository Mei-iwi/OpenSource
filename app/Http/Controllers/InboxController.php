<?php

namespace App\Http\Controllers;

use App\Models\AdminMessage;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InboxController extends Controller
{
    public function index(Request $request): View
    {
        $messages = AdminMessage::query()
            ->whereJsonContains('recipient_user_ids', $request->user()->id)
            ->with('admin:id,name,email')
            ->latest('sent_at')
            ->paginate(10);

        return view('communications.inbox', compact('messages'));
    }
}
