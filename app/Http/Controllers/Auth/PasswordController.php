<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class PasswordController extends Controller
{
    /**
     * Update the user's password.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validateWithBag('updatePassword', [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('status', 'password-updated');
    }

    public function showResetForm($token)
    {
        return view('auth.reset-password', ['token' => $token]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'password' => 'required|string|min:8|confirmed',
            'token' => 'required'
        ]);

        // Attempt to find the user using the email and token
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withErrors(['error' => 'User not found.']);
        }

        // Check if the token is valid
        if (!Password::tokenExists($user, $request->token)) {
            return back()->withErrors(['error' => 'Invalid token.']);
        }

        // Update the password
        $user->forceFill([
            'password' => Hash::make($request->password),
        ])->save();

        // Redirect to the desired URL after password reset
        return redirect()->away('https://xmeet.algohat.com/')->with('message', 'Password reset successfully.');
    }
}
