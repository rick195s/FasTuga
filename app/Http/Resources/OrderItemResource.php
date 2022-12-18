<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
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
            "local_number" => $this->order->ticket_number + " - " + $this->order_local_number,
            "status" => $this->statusToString(),
            "price_on_purchase" => $this->price,
            "notes" => $this->notes,
            "preparation_by" => $this->preparation_by,
            "product" => $this->product_id
        ];
    }
}
