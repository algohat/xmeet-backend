<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminAuthController extends Controller
{

    public function loginForm()
    {
        return view('backend.auth.login');

    }

    public function login(Request $request)
    {
        if (Auth::guard('admin')->attempt($request->only('email', 'password'))) {
            return redirect()->route('admin.dashboard');
        }
        return back()->withErrors(['email' => 'Invalid credentials']);
    }

}
