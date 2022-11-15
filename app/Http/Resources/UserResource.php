<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'type' => $this->type,
            'typeToString' => $this->typeToString(),
            'blocked' => $this->blocked ? true : false,
            'photo_url' =>  $this->photo_url ? asset('storage/fotos/' . $this->photo_url) : '',
        ];
    }
}
