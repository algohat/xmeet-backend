<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

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
}
