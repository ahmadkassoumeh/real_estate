<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Friend;
use App\Models\User;

class FriendController extends Controller
{
    public function add(Request $request)
    {
        $userId = $request->user()->id;
        $friendId = $request->friend_id;

        if (!User::where('id', $friendId)->exists()) {
            return response()->json(['message' => 'User not found'], 404);
        }

        if (Friend::where('user_id', $userId)->where('friend_id', $friendId)->exists()) {
            return response()->json(['message' => 'Already friends or request pending'], 409);
        }

        if ($userId == $friendId) {
            return response()->json(['message' => 'Cannot add yourself as friend'], 400);
        }

        Friend::create([
            'user_id' => $request->user()->id,
            'friend_id' => $request->friend_id,
        ]);

        Friend::create([
            'user_id' => $request->friend_id,
            'friend_id' => $request->user()->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Friend added successfully',
            'data' => [
                'friend_id' => (int)$request->friend_id,
                'created_at' => now()->toIso8601String()
            ],
            'code' => 200
        ], 200);
    }
}
