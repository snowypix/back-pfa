<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    function login(Request $request)
    {

        // Validate the request data
        $validatedData = $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        // Attempt to authenticate the user
        if (Auth::attempt($validatedData)) {
            $user = User::where('email', $request->input('email'))->first();
            $header = base64_encode(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
            $payload = base64_encode(json_encode(['sub' => $user->id, 'email' => $user->email]));
            $signature = base64_encode(hash_hmac('sha256', $header . '.' . $payload, 'your-secret-key', true));
            $response = new Response('Logged in');
            return response()->json(['token' => $header . '.' . $payload . '.' . $signature], 201);
        } else {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }
    }
    function register(Request $request)
    {
        // Validate the request data
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|min:8',
        ]);

        // Create a new user
        $user = new User();
        $user->name = $validatedData['name'];
        $user->email = $validatedData['email'];
        $user->password = Hash::make($validatedData['password']);
        $user->save();

        // Return a response
        return response()->json(['message' => 'User registered successfully'], 201);
    }
}
