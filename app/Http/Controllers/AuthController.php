<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use App\Models\User;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $data = $request->validate([
            'username' => ['required', 'digits:6', 'unique:users,username'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'lastname' => ['required', 'string', 'max:255'],
            'firstname' => ['required', 'string', 'max:255'],
            'patronymic' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:255']
        ]);

        $user = User::create([
            'username' => $data['username'],
            'password' => Hash::make($data['password']),
            'lastname' => $data['lastname'],
            'firstname' => $data['firstname'],
            'patronymic' => $data['patronymic'] ?? null,
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'group_id' => 1,
            'role' => 'admin'
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json(['token' => $token, 'user' => $user], 201);
    }

    public function student_register(Request $request)
    {
        $data = $request->validate([
            'lastname' => ['required', 'string', 'max:255'],
            'firstname' => ['required', 'string', 'max:255'],
            'patronymic' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', 'unique:users,email']
        ]);

        do {
            $username = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        } while (User::where('username', $username)->exists());

        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789!@#$%^&*';
        $plainPassword = '';
        for ($i = 0; $i < 12; $i++) {
            $plainPassword .= $alphabet[random_int(0, strlen($alphabet) - 1)];
        }

        $user = User::create([
            'username' => $username,
            'password' => Hash::make($plainPassword),
            'lastname' => $data['lastname'],
            'firstname' => $data['firstname'],
            'patronymic' => $data['patronymic'] ?? null,
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'group_id' => 1,
            'role' => 'user'
        ]);

        return response()->json([
            'user' => $user,
            'username' => $username,
            'password' => $plainPassword
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|numeric',
            'password' => 'required'
        ]);

        if (!Auth::attempt($request->only('username', 'password'))) {
            return response()->json(['message' => 'Неверные учетные данные'], 401);
        }

        $user = User::where('username', $request->username)->firstOrFail();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json(['token' => $token, 'user' => $user]);
    }
}
