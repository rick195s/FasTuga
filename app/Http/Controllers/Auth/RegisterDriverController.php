<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterDriverRequest;
use App\Http\Resources\UserResource;
use App\Models\Customer;
use App\Models\Driver;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;


class RegisterDriverController extends Controller
{
    /**
     * Handle an incoming registration request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(RegisterDriverRequest $request)
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

        event(new Registered($user));

        Auth::login($user);

        $user = auth()->user();
        $token = $user->createToken("authToken")->accessToken;

        return response(["user" => new UserResource($user), "token" => $token]);
    }
}
