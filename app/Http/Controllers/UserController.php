<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    // List all users
    public function index()
    {
        $users = User::all();
        return response()->json($users);
    }

    // Show a single user
    public function show($id)
    {
        $user = User::findOrFail($id);
        return response()->json($user);
    }

    // Create a new user
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'contact_number' => 'nullable|string|max:20',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role' => 'nullable|string|in:user,admin',
        ]);

        $user = User::create([
            'name' => $request->name,
            'contact_number' => $request->contact_number,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role ?? 'user',
        ]);

        return response()->json($user, 201);
    }

    // Update an existing user
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'contact_number' => 'nullable|string|max:20',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:6',
            'role' => 'nullable|string|in:user,admin',
        ]);

        if ($request->has('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->update($request->only('name', 'contact_number', 'email', 'role'));

        return response()->json($user);
    }

    // Delete a user
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return response()->json(['message' => 'User deleted successfully.']);
    }
}