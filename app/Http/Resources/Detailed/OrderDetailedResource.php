<?php

namespace App\Http\Resources\Detailed;

use App\Http\Resources\CustomerResource;
use App\Http\Resources\OrderItemResource;
use App\Http\Resources\UserResource;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderDetailedResource extends JsonResource
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
            "ticket_number" => $this->ticket_number,
            "status" => $this->statusToString(),
            "total_price" => $this->total_price,
            "total_paid" => $this->total_paid,
            "total_paid_with_points" => $this->total_paid_with_points,
            "points_gained" => $this->points_gained,
            "points_used_to_pay" => $this->points_used_to_pay,
            "payment_type" => $this->payment_type,
            "payment_reference" => $this->payment_reference,
            "date" => $this->date,
            "customer" => new CustomerResource($this->customer()->withTrashed()->first()),
            "delivery_by" => new UserResource($this->deliveredBy()->withTrashed()->first()),
            "order_items" => OrderItemResource::collection($this->orderItems),
        ];
    }
}
