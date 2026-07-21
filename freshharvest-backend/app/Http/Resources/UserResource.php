<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => [
                'street' => $this->street,
                'suite' => $this->suite,
                'cityStateZip' => $this->city_state_zip,
            ],
            'role' => $this->getRoleNames()->first(),
            'created_at' => $this->created_at,
        ];
    }
}