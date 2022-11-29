<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateDriverRequest;
use Illuminate\Http\Request;
use App\Http\Resources\DriverResource;
use App\Http\Resources\UserResource;
use App\Models\Driver;
use Illuminate\Support\Facades\Hash;

class DriverController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateDriverRequest $request, Driver $driver)
    {
        $validated = $request->validated();

        if (isset($validated['password'])) {
            $driver->user->password = Hash::make($validated['password']);
            $driver->user->save();
        }

        if (isset($validated['photo'])) {
            $ext = $validated['photo']->extension();
            $photoName = "driver" . $driver->id . "_" . uniqid() . '.' . $ext;
            $validated['photo']->storeAs('public/fotos', $photoName);
            $driver->user->photo_url = $photoName;
            $driver->user->save();
        }

        $driver->update($validated);

        DriverResource::withoutWrapping();
        return new DriverResource($driver);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
