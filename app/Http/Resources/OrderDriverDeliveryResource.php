<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderDriverDeliveryResource extends JsonResource
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
            'order_driver_delivery_id' => $this->id,
            'delivery_location' => $this->delivery_location,
            'tax_fee' => $this->tax_fee,
            'delivery_started_at' => $this->orderDriverDelivery,
            //'delivery_started_at' => $this->delivery_started_at,
            'delivery_ended_at' => $this->delivery_ended_at,
            'delivered_by' => $this->order->delivered_by,
            'id' => $this->order->id,
            'payment_type'=> $this->order->payment_type,
            'ticket_number'=> $this->order->ticket_number,
            'distance'=> $this->distance,
            

            //'product_type'=> $this->order->orderItems->product->type
        ];
    }
}
