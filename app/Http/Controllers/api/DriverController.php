<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateDriverRequest;
use Illuminate\Http\Request;
use App\Http\Resources\DriverResource;
use App\Http\Resources\OrderDriverDeliveryResource;
use App\Models\Driver;
use Illuminate\Support\Facades\Hash;

class DriverController extends Controller
{
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateDriverRequest $request, Driver $driver)
    {
        $this->authorize('update', $driver);
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


    public function orders(Driver $driver)
    {
        $this->authorize('orders', $driver);
        return OrderDriverDeliveryResource::collection($driver->ordersDriverDelivery()
            ->whereRelation('order', 'status', '=', 'D')
            ->orderBy('delivery_ended_at', 'desc')->paginate(10));
    }
}
