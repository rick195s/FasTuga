<?php

namespace App\Http\Controllers\api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\CustomerResource;
use App\Http\Resources\DriverResource;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{

    public function __construct()
    {
        $this->authorizeResource(User::class, 'user');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return UserResource::collection(User::paginate(5));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(RegisterUserRequest $request)
    {
        return (new AuthController)->register($request);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {
        return new UserResource($user);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        $validated = $request->validated();

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        $user->update($validated);

        return new UserResource($user);
    }


    public function update_photo(UpdateUserRequest $request, User $user)
    {
        $validated = $request->validated();

        if (isset($validated['photo'])) {
            $ext = $validated['photo']->extension();
            $photoName = $user->id . "_" . uniqid() . '.' . $ext;
            $validated['photo']->storeAs('public/fotos', $photoName);
            $user->photo_url = $photoName;
        }

        $user->save();

        return new UserResource($user);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        $user->delete();
        return new UserResource($user);
    }


    public function show_me(Request $request)
    {
        // FasTugaDriver integration
        if ($request->user()->driver) {
            // removes the 'data' key from the response
            // https://laravel.com/docs/9.x/eloquent-resources#data-wrapping
            DriverResource::withoutWrapping();
            return new DriverResource($request->user()->driver);
        }

        if ($request->user()->customer) {
            return new CustomerResource($request->user()->customer);
        }


        return new UserResource($request->user());
    }



    public function toggle_blocked(Request $request, User $user)
    {
        $user->blocked = !$user->blocked;
        $user->save();

        return new UserResource($user);
    }
}
