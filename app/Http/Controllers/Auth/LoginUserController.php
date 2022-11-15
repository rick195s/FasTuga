<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LoginUserController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->only("email", "password");

        if (auth()->attempt($credentials)) {
            $user = auth()->user();
            $token = $user->createToken("authToken")->accessToken;

            return response(["user" => $user, "token" => $token]);
        }

        return response(["message" => "Invalid credentials"]);
    }
}
