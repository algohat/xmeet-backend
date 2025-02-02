<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserlistController extends Controller
{
    public function index()
    {

        $users = User::query()->latest() ->get();


        return view('backend.users.index',compact('users'));

    }

    public function delete(Request $request,$id)
    {

        $user = User::findOrFail($id);
        $user->delete();

        return to_route('user.index')->with('success','User deleted successfully');


    }

    public function blockConfirm($sender_identifier, $receiver_identifier)
    {
        // Retrieve the sender and receiver users by their identifiers
        $sender = User::where('identifier', $sender_identifier)->first();
        $receiver = User::where('identifier', $receiver_identifier)->first();

        // Check if both users exist
        if (!$sender || !$receiver) {
            return redirect()->back()->with('error', 'User not found.');
        }

        // Check if the receiver has already blocked the sender
        $blockExists = DB::table('user_blocks')
            ->where('user_id', $sender->id)
            ->where('reporter_id', $receiver->id)
            ->exists();

        if ($blockExists) {
            return redirect()->back()->with('error', 'User is already blocked.');
        }

        return view('backend.users.block-confirm', compact('sender_identifier', 'receiver_identifier', 'sender'));
    }

    public function userBlock(Request $request, $sender_identifier, $receiver_identifier)
    {
        $sender = User::where('identifier', $sender_identifier)->first();
        $receiver = User::where('identifier', $receiver_identifier)->first();

        if (!$sender || !$receiver) {
            return redirect()->back()->with('error', 'User not found.');
        }

        $blockExists = DB::table('user_blocks')
            ->where('user_id', $sender->id)
            ->where('reporter_id', $receiver->id)
            ->exists();

        if ($blockExists) {
            return redirect()->back()->with('error', 'User is already blocked.');
        }

        DB::table('user_blocks')->insert([
            'user_id' => $sender->id,
            'reporter_id' => $receiver->id,
            'reason' => $request->reason,
            'created_at' => now(),
        ]);

        return redirect()->away('https://xmeet.algohat.com/')->with('message', 'Password reset successfully.');
    }
}
