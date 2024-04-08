<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    function generateJwt(Request $request)
    {
        $user = User::where('email', $request->input('email'))->first();
        $header = base64_encode(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
        $payload = base64_encode(json_encode(['sub' => $user->id, 'email' => $user->email]));
        $signature = base64_encode(hash_hmac('sha256', $header . '.' . $payload, 'your-secret-key', true));
        dd($header . '.' . $payload);
        dd(base64_encode(hash_hmac('sha256', $header . '.' . $payload, 'your-secret-key', true)));
        return $header . '.' . $payload . '.' . $signature;
    }
}
