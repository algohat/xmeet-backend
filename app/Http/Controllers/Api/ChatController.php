<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Chat;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        $receiverIdCheck = User::find($receiverId);

        if (!$receiverIdCheck) {
            return response()->json(['errors' => ['receiver_id' => 'Invalid receiver ID']], 422);
        }

        if ($receiverId == Auth::user()->id) {
            return response()->json(['errors' => ['receiver_id' => 'You cannot send message to yourself']], 422);
        }

        Chat::create([
            'sender_id' => Auth::user()->id,
            'receiver_id' => $receiverId,
            'message' => $request->message,
        ]);

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
        $messages = Chat::where(function($query) use ($userId, $receiverId) {
            $query->where('sender_id', $userId)->where('receiver_id', $receiverId)
                ->orWhere('sender_id', $receiverId)->where('receiver_id', $userId);
        })->orderBy('created_at', 'asc')->get();

        return response()->json([
            'messages' => $messages
        ], 200);
    }
}
