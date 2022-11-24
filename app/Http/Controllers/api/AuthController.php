<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\User;
use App\Models\Customer;
use App\Http\Resources\DriverResource;
use App\Http\Resources\UserResource;
use App\Http\Resources\CustomerResource;
use App\Http\Requests\Auth\RegisterUserRequest;
use Illuminate\Support\Facades\Hash;

const PASSPORT_SERVER_URL = "http://localhost";
const CLIENT_ID = 2;
const CLIENT_SECRET = 'IeB9DEYv6KjCTcrGI1mzrufJKmJyjgC68jjImUnE';

class AuthController extends Controller
{
    private function passportAuthenticationData($username, $password) {
       return [
           'grant_type' => 'password',
           'client_id' => CLIENT_ID,
           'client_secret' => CLIENT_SECRET,
           'username' => $username,
           'password' => $password,
           'scope' => ''
       ];
    }

    public function login(Request $request)
    {
        try {
            request()->request->add($this->passportAuthenticationData($request->username, $request->password));
            $request = Request::create(PASSPORT_SERVER_URL . '/oauth/token', 'POST');
            $response = Route::dispatch($request);
            $errorCode = $response->getStatusCode();
            $auth_server_response = json_decode((string) $response->content(), true);
            return response()->json($auth_server_response, $errorCode);
        }
        catch (\Exception $e) {
            return response()->json('Authentication has failed!', 401);
        }
    }

    public function registerUser(RegisterUserRequest $request)
    {

        $validated = $request->validated();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'type' => $validated['type'],
            'blocked' => 0
        ]);

        if ($validated['type'] == 'C') {
            Customer::create([
                'user_id' => $user->id,
                'phone' => $validated['phone'],
                'nif' => $validated['nif'],
                'points' => 0
            ]);

            return response()->json(new CustomerResource($user->customer), 201);
        }

        return response()->json(new UserResource($user), 201);
        
    }


    public function logout(Request $request)
    {
        $accessToken = $request->user()->token();
        $token = $request->user()->tokens->find($accessToken);
        $token->revoke();
        $token->delete();
        return response(['msg' => 'Token revoked'], 200);
    }

    
}