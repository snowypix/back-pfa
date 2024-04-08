<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Support\Facades\Auth;

class VerifyJwt
{
    public function verifyJwt($token)
    {
        list($header, $payload, $signature) = explode('.', $token);
        $header = str_replace('Bearer ', '', $header);
        $validSignature = (base64_encode(hash_hmac('sha256', $header . '.' . $payload, 'your-secret-key', true)) === $signature);
        if (!$validSignature) {
            return false;
        }
        $payload = json_decode(base64_decode($payload));

        // Check if the token is expired or other validation rules

        // Retrieve user or any other data from the payload
        $user = User::find($payload->sub);

        return $user;
    }
    public function handle($request, Closure $next)
    {
        $token = $request->header('Authorization');

        if (!$token) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $user = $this->verifyJwt($token);
        if (!$user) {
            return response()->json(['error' => 'Invalid token'], 401);
        }

        Auth::login($user);

        return $next($request);
    }
}
