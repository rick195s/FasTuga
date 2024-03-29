<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
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
            "created_at" => $this->created_at->format('Y-m-d H:i:s'),
            "customer_id" => $this->customer_id,
            "customer_name" => $this->customer ? $this->customer->user()->withTrashed()->first()->name : null,
            "delivery_by" => $this->delivered_by,
        ];
    }
}
