<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
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
            "customer_id" => $this->id,

            $this->merge(function () {
                return new UserResource($this->user()->withTrashed()->first());
            }),

            "phone" => $this->phone,
            "points" => $this->points,
            "nif" => $this->nif,
            "default_payment_type" => $this->default_payment_type,
            "default_payment_id" => $this->defaultPaymentId(),
        ];
    }
}
