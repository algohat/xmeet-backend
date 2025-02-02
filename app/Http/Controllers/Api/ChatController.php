<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Chat;
use App\Models\ChatOpen;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class ChatController extends Controller
{
    public function setMessage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'receiver_identifier' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $receiverId = decryptUserHash($request->receiver_identifier);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid receiver identifier'], 400);
        }
        $receiverIdCheck = User::where('id', $receiverId)->where('is_disable', 0)->first();

        if (!$receiverIdCheck) {
            return response()->json(['errors' => ['receiver_id' => 'Invalid receiver ID']], 422);
        }

        if ($receiverId == Auth::user()->id) {
            return response()->json(['errors' => ['receiver_id' => 'You cannot send message to yourself']], 422);
        }

        $isFirstMessage = !ChatOpen::where(function ($query) use ($receiverId) {
            $query->where('sender_id', Auth::user()->id)
                ->where('receiver_id', $receiverId);
        })
            ->orWhere(function ($query) use ($receiverId) {
                $query->where('sender_id', $receiverId)
                    ->where('receiver_id', Auth::user()->id);
            })
            ->exists();

        // Create the chat message
        $chat = Chat::create([
            'sender_id' => Auth::user()->id,
            'receiver_id' => $receiverId,
            'message' => $request->message,
        ]);

        if ($isFirstMessage) {
            // Create the chat open record
            ChatOpen::create([
                'chat_id' => $chat->id,
                'sender_id' => Auth::user()->id,
                'receiver_id' => $receiverId,
                'is_opened' => 1,
                'opened_at' => now()
            ]);

            //$adminEmail = DB::table('admins')->where('id', 1)->value('email');
            $adminEmail = 's.vonberg13@googlemail.com';

            $subject = "[" . config('app.name') . "][CHAT] Von: " . Auth::user()->identifier .
                " | An: " . $request->receiver_identifier .
                " | Chat-ID: " . $chat->id;

            $sender = Auth::user();

            // Send the email notification to Admin
            try {
                Mail::send('emails.chat_open_notification', [
                    'sender' => $sender,
                    'receiver' => $receiverIdCheck,
                    'chat_text' => $request->message,
                ], function ($message) use ($adminEmail, $subject) {
                    $message->to($adminEmail)
                        ->subject($subject);
                });
            } catch (\Exception $e) {
                \Log::error('Failed to send first-time message notification to admin: ' . $e->getMessage());
            }

            // Send the email notification to Receiver
            try {
                Mail::send('emails.chat_open_notification', [
                    'sender' => $sender,
                    'receiver' => $receiverIdCheck,
                    'chat_text' => $request->message,
                ], function ($message) use ($receiverIdCheck, $subject) {
                    $message->to($receiverIdCheck->email)
                        ->subject($subject);
                });
            } catch (\Exception $e) {
                \Log::error('Failed to send first-time message notification to receiver: ' . $e->getMessage());
            }
        }


        return response()->json(['message' => 'Message set successfully'], 200);
    }

    public function viewChat(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'receiver_identifier' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $receiverId = decryptUserHash($request->receiver_identifier);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid or expired identifier.'], 400);
        }

        $receiver = User::find($receiverId);
        if (!$receiver) {
            return response()->json(['error' => 'Receiver not found.'], 404);
        }

        $userId = auth()->id();
        $messages = Chat::where(function ($query) use ($userId, $receiverId) {
            $query->where('sender_id', $userId)->where('receiver_id', $receiverId)
                ->orWhere('sender_id', $receiverId)->where('receiver_id', $userId);
        })->orderBy('created_at', 'asc')->get();

        return response()->json([
            'messages' => $messages
        ], 200);
    }
}
