<?php

namespace App\Http\Resources\Detailed;

use App\Http\Resources\OrderResource;
use App\Http\Resources\ProductResource;
use App\Http\Resources\UserResource;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemDetailedResource extends JsonResource
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
            "id" => $this->id,
            "order_id" => $this->order_id,
            "local_number" => $this->order->ticket_number . "-" . $this->order_local_number,
            "status" => $this->statusToString(),
            "price_on_purchase" => $this->price,
            "notes" => $this->notes,
            "order_ticket_number" => $this->order()->first()->ticket_number,
            "order_status" => $this->order()->first()->status,
            "preparation_by" => new UserResource($this->preparationBy()->withTrashed()->first()),
            "product" => new ProductResource($this->product()->withTrashed()->first()),
        ];
    }
}
