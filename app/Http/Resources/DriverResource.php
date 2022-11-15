<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DriverResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            "id" => $this->user->id,
            "driver_id" => $this->id,

            $this->merge(function () {
                return new UserResource($this->user()->withTrashed()->first());
            }),

            "phone" => $this->phone,
            "license_plate" => $this->license_plate,

        ];
    }
}
