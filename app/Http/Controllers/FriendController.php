<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class FriendController extends Controller
{
    public function sendFriendRequest(User $recipient)
    {
        auth()->user()->friends()->attach($recipient);

        return redirect()->back()->with('success', 'Friend request sent.');
    }

    public function acceptFriendRequest(User $sender)
    {
        auth()->user()->friends()->attach($sender);

        return redirect()->back()->with('success', 'Friend request accepted.');
    }

    public function showFriends()
    {
        $friends = auth()->user()->friends;

        return view('friends.index', compact('friends'));
    }
}
