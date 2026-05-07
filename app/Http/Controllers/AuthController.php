<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // ─────────────────────────────────────────
    // Login — JSON response for popup
    // ─────────────────────────────────────────
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        if (Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();

            return response()->json([
                'success'  => true,
                'role'     => $user->role,
                'name'     => $user->name,
                'redirect' => $user->role === 'admin'
                    ? route('admin.dashboard')
                    : route('client.index'),
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'These credentials do not match our records.',
        ], 401);
    }

    // ─────────────────────────────────────────
    // Register — JSON response for popup
    // ─────────────────────────────────────────
    public function register(Request $request)
    {
        $validator = validator($request->all(), [
            'name'                  => 'required|string|max:255',
            'email'                 => 'required|email|unique:users,email',
            'contact_number'        => 'nullable|string|max:20',
            'password'              => 'required|string|min:6|confirmed',
            'password_confirmation' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()->toArray(),
            ], 422);
        }

        $user = User::create([
            'name'           => $request->name,
            'email'          => $request->email,
            'contact_number' => $request->contact_number,
            'password'       => Hash::make($request->password),
            'role'           => 'user',
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return response()->json([
            'success'  => true,
            'role'     => 'user',
            'name'     => $user->name,
            'redirect' => route('client.index'),
        ]);
    }

    // ─────────────────────────────────────────
    // Logout
    // ─────────────────────────────────────────
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('client.index');
    }
}