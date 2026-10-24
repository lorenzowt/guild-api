<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Requests\RegisterUserRequest;

class AuthController extends Controller
{
    public function register(RegisterUserRequest $request)
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
        ]);
        
        return response()->json([
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'created_at' =>$user->created_at,
        ], 201);
    }
}
