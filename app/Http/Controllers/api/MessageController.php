<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class MessageController extends Controller
{
  
    public function store(Request $request)
    {
        try {
            $rules = [
                'receiver_id' => 'required|exists:users,id',
                'message' => 'required|string',
                'attachment_url' => 'nullable|string|max:255',
            ];

            $messages = [
                'receiver_id.required' => 'Alıcı ID alanı zorunludur',
                'receiver_id.exists' => 'Geçerli bir alıcı ID giriniz',
                'message.required' => 'Mesaj alanı zorunludur',
                'attachment_url.max' => 'Ek dosya URL’si 255 karakterden uzun olamaz',
            ];

            $request->validate($rules, $messages);

            $currentUser = auth()->user();

            if ($request->receiver_id == $currentUser->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kendinize mesaj gönderemezsiniz',
                    'data' => null,
                ], 403);
            }

            $message = Message::create([
                'sender_id' => $currentUser->id,
                'receiver_id' => $request->receiver_id,
                'message' => $request->message,
                'attachment_url' => $request->attachment_url,
                'sent_at' => now(),
                'is_delivered' => false, 
            ]);



            return response()->json([
                'success' => true,
                'message' => 'Mesaj başarıyla gönderildi',
                'data' => $message->load(['sender', 'receiver']),
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Giriş bilgileri geçersiz',
                'data' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Mesaj gönderilemedi: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

   
    public function getConversation(Request $request, $userId)
    {
        try {
            $currentUser = auth()->user();

            $otherUser = User::findOrFail($userId);

            $messages = Message::where(function ($query) use ($currentUser, $userId) {
                $query->where('sender_id', $currentUser->id)
                      ->where('receiver_id', $userId);
            })->orWhere(function ($query) use ($currentUser, $userId) {
                $query->where('sender_id', $userId)
                      ->where('receiver_id', $currentUser->id);
            })->orderBy('sent_at', 'asc')
              ->get();

            $messagesToUpdate = $messages->where('receiver_id', $currentUser->id)
                                        ->where('is_delivered', false);
            foreach ($messagesToUpdate as $msg) {
                $msg->update(['is_delivered' => true]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Konuşma başarıyla getirildi',
                'data' => $messages->load(['sender', 'receiver']),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Konuşma getirilemedi: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

   
    public function getConversations()
    {
        try {
            $currentUser = auth()->user();

            $sentMessages = Message::where('sender_id', $currentUser->id)
                ->select('receiver_id')
                ->distinct();
            
            $receivedMessages = Message::where('receiver_id', $currentUser->id)
                ->select('sender_id')
                ->distinct();

            $userIds = $sentMessages->pluck('receiver_id')
                ->merge($receivedMessages->pluck('sender_id'))
                ->unique()
                ->values();

            $conversations = [];
            foreach ($userIds as $userId) {
                $otherUser = User::find($userId);
                if (!$otherUser) continue;

                $lastMessage = Message::where(function ($query) use ($currentUser, $userId) {
                    $query->where('sender_id', $currentUser->id)
                          ->where('receiver_id', $userId);
                })->orWhere(function ($query) use ($currentUser, $userId) {
                    $query->where('sender_id', $userId)
                          ->where('receiver_id', $currentUser->id);
                })->orderBy('sent_at', 'desc')
                  ->first();

                if ($lastMessage) {
                    $unreadCount = Message::where('receiver_id', $currentUser->id)
                        ->where('sender_id', $userId)
                        ->whereNull('read_at')
                        ->count();

                    $conversations[] = [
                        'user' => $otherUser,
                        'last_message' => $lastMessage,
                        'unread_count' => $unreadCount,
                    ];
                }
            }

            usort($conversations, function ($a, $b) {
                return strtotime($b['last_message']->sent_at) - strtotime($a['last_message']->sent_at);
            });

            return response()->json([
                'success' => true,
                'message' => 'Konuşma listesi başarıyla getirildi',
                'data' => $conversations,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Konuşma listesi getirilemedi: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

   
    public function markAsRead($messageId)
    {
        try {
            $message = Message::findOrFail($messageId);
            $currentUser = auth()->user();

            if ($message->receiver_id != $currentUser->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bu mesajı okundu olarak işaretleme yetkiniz yok',
                    'data' => null,
                ], 403);
            }

            if ($message->read_at) {
                return response()->json([
                    'success' => true,
                    'message' => 'Mesaj zaten okundu olarak işaretlenmiş',
                    'data' => $message->load(['sender', 'receiver']),
                ]);
            }

            $message->update([
                'read_at' => now(),
                'is_delivered' => true, 
            ]);


            return response()->json([
                'success' => true,
                'message' => 'Mesaj okundu olarak işaretlendi',
                'data' => $message->load(['sender', 'receiver']),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Mesaj okundu olarak işaretlenemedi: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

   
    public function destroy($messageId)
    {
        try {
            $message = Message::findOrFail($messageId);
            $currentUser = auth()->user();

            if ($message->sender_id != $currentUser->id && $message->receiver_id != $currentUser->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bu mesajı silme yetkiniz yok',
                    'data' => null,
                ], 403);
            }

            $message->delete();


            return response()->json([
                'success' => true,
                'message' => 'Mesaj başarıyla silindi',
                'data' => null,
            ], 204);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Mesaj silinemedi: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }
}