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
            ->select('id', 'username', 'email', 'created_at' , 'profile_image')
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
                //

                return [
                    'id' => $user->id,
                    'name' => $user->username,
                    'email' => $user->email,
                    'unread_count' => $unreadCount,
                    'last_message_at' => $lastMessage ? $lastMessage->created_at : null,
                    'profile_image_url' => asset('storage/users/' . $user->profile_image),
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

        broadcast(new MessageSent($message))->toOthers();

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $message->id,
                'message' => $message->message,
                'sender' => [
                    'id' => $message->sender->id,
                    'name' => $message->sender->username,
                ],
                'receiver_id' => $message->receiver_id,
                'createdAt' => $message->created_at,
            ]
        ], 201);
    }

    public function getMessages(Request $request, $userId)
    {
        $authUser = $request->user();

        $messages = Message::where(function ($query) use ($authUser, $userId) {
            $query->where('sender_id', $authUser->id)
                ->where('receiver_id', $userId);
        })
            ->orWhere(function ($query) use ($authUser, $userId) {
                $query->where('sender_id', $userId)
                    ->where('receiver_id', $authUser->id);
            })
            ->with('sender:id,username')
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($message) use ($authUser) {
                return [
                    'id' => $message->id,
                    'message' => $message->message,
                    'sender_id' => $message->sender_id,
                    'receiver_id' => $message->receiver_id,
                    'is_mine' => $message->sender_id === $authUser->id,
                    'created_at' => $message->created_at->toDateTimeString(),
                    'sender' => [
                        'id' => $message->sender->id,
                        'name' => $message->sender->username,
                    ],
                ];
            });

        // تعليم الرسائل كمقروءة
        Message::where('sender_id', $userId)
            ->where('receiver_id', $authUser->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json([
            'success' => true,
            'chat' => [
                'with_user_id' => (int) $userId,
            ],
            'messages' => $messages,
        ]);
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
