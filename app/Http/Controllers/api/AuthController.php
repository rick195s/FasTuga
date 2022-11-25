<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\User;
use App\Models\Customer;
use App\Models\Driver;
use App\Http\Resources\DriverResource;
use App\Http\Resources\UserResource;
use App\Http\Resources\CustomerResource;
use App\Http\Requests\Auth\RegisterUserRequest;
use App\Http\Requests\Auth\RegisterDriverRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

const PASSPORT_SERVER_URL = "http://localhost";
const CLIENT_ID = 2;
const CLIENT_SECRET = 'IeB9DEYv6KjCTcrGI1mzrufJKmJyjgC68jjImUnE';

class AuthController extends Controller
{
    private function passportAuthenticationData($username, $password)
    {
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
        $request->username = $request->email;
        try {
            request()->request->add($this->passportAuthenticationData($request->username, $request->password));
            $request = Request::create(PASSPORT_SERVER_URL . '/oauth/token', 'POST');
            $response = Route::dispatch($request);
            $errorCode = $response->getStatusCode();
            $auth_server_response = json_decode((string) $response->content(), true);
            return response()->json($auth_server_response, $errorCode);
        } catch (\Exception $e) {
            return response()->json('Authentication has failed!', 401);
        }
    }



    // FasTugaDriver Integration
    public function loginDriver(Request $request)
    {
        if (!User::where('email', $request->email)->first()->driver) {
            return response()->json('User not a Driver!', 401);
        }

        return $this->login($request);
    }

    public function register(RegisterUserRequest $request)
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
        }

        $request->username = $validated['email'];
        return $this->login($request);
    }


    // FasTugaDriver integration
    public function registerDriver(RegisterDriverRequest $request)
    {
        $validated = $request->validated();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'type' => 'ED',
            'blocked' => 0,
        ]);

        Driver::create([
            'user_id' => $user->id,
            'phone' => $validated['phone'],
            'license_plate' => $validated['license_plate'],
        ]);

        $request->username = $validated['email'];
        return $this->login($request);
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
