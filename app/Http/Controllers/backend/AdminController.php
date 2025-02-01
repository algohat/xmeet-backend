<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use App\Models\ChatOpen;
use App\Models\User;

class AdminController extends Controller
{
    public function index(){

        $data['users'] = User::query()->count();
        $data['total_chat'] = ChatOpen::query()->count();
        return view('backend.layouts.home', $data);
    }

    public function logout()
    {
        Auth('admin')->logout();
        return to_route('admin.login.form');

    }
}
