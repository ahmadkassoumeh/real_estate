<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Message;
use App\Events\MessageSent;
use App\Models\Friend;
use Illuminate\Support\Facades\Auth;
use App\Models\User;


class ChatController extends Controller
{
    public function users(Request $request)
    {
        $currentUserId = $request->user()->id;

        $friendIds = Friend::where('user_id', $currentUserId)
        ->pluck('friend_id');

        $users = User::whereIn('id', $friendIds)
            ->select('id', 'username', 'email', 'created_at')
            ->get()
            ->map(function ($user) use ($currentUserId) {
                $unreadCount = Message::where('sender_id', $user->id)
                    ->where('receiver_id', $currentUserId)
                    ->where('is_read', false)
                    ->count();

                $lastMessage = Message::where(function ($query) use ($currentUserId, $user) {
                    $query->where('sender_id', $currentUserId)
                          ->where('receiver_id', $user->id);
                })->orWhere(function ($query) use ($currentUserId, $user) {
                    $query->where('sender_id', $user->id)
                          ->where('receiver_id', $currentUserId);
                })->latest()->first();

                return [
                    'id' => $user->id,
                    'name' => $user->username,
                    'email' => $user->email,
                    'unread_count' => $unreadCount,
                    'last_message_at' => $lastMessage ? $lastMessage->created_at : null,
                ];
            })
            // ترتيب حسب آخر رسالة (الأحدث أولاً)
            ->sortByDesc('last_message_at')
            ->values();

        return response()->json($users);
    }

    // إرسال رسالة
    public function sendMessage(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'message' => 'required|string',
        ]);

        $message = Message::create([
            'sender_id' => $request->user()->id,
            'receiver_id' => $request->receiver_id,
            'message' => $request->message,
        ]);

       
        return response()->json($message->load(['sender', 'receiver']), 201);
    }

    public function getMessages(Request $request, $userId)
    {
        $messages = Message::where(function ($query) use ($request, $userId) {
            $query->where('sender_id', $request->user()->id)
                  ->where('receiver_id', $userId);
        })->orWhere(function ($query) use ($request, $userId) {
            $query->where('sender_id', $userId)
                  ->where('receiver_id', $request->user()->id);
        })
        ->with(['sender', 'receiver'])
        ->orderBy('created_at', 'asc')
        ->get();

        // تعليم الرسائل كمقروءة
        Message::where('sender_id', $userId)
            ->where('receiver_id', $request->user()->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json($messages);
    }

    // عدد الرسائل غير المقروءة الكلي
    public function unreadCount(Request $request)
    {
        $count = Message::where('receiver_id', $request->user()->id)
            ->where('is_read', false)
            ->count();

        return response()->json(['unread_count' => $count]);
    }
}
