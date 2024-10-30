<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use App\Models\User;

class AdminController extends Controller
{
    public function index(){

        $users = User::query()->count();
        return view('backend.layouts.home', compact('users'));
    }

    public function logout()
    {
        Auth('admin')->logout();
        return to_route('admin.login.form');

    }
}
