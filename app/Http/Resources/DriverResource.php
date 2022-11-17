<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\UserResource;

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
            $this->merge(function () {
                return new UserResource($this->user()->withTrashed()->first());
            }),
            "driver_id" => $this->id,
            "phone" => $this->phone,
            "license_plate" => $this->license_plate,

        ];
    }
}
