<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Http\Resources\DriverResource;
use Illuminate\Http\Request;

class LoginUserController extends Controller
{
    private function login(Request $request)
    {
        $credentials = $request->only("email", "password");

        if (auth()->attempt($credentials)) {
            $user = auth()->user();
            $token = $user->createToken("authToken")->accessToken;

            return [$user, $token];
        }

        return [null,null];
    }

    public function user(Request $request){
        [$user, $token] = $this->login($request);

        if ($user) {
            return response(["user" => new UserResource($user), "token" => $token]);
        }

        return  response(["message" => "Invalid credentials"], 401);
    }

    public function driver(Request $request){
        [$user, $token] = $this->login($request);

        if ($user->driver) {
            return response(["driver" => new DriverResource($user->driver), "token" => $token]);
        }else{
            return  response(["message" => "User not a Driver"], 401);
        }

        return  response(["message" => "Invalid credentials"], 401);
    }
}
