<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'product_name' => $this->product_name_snapshot,
            'price' => $this->price_snapshot,
            'game_account_id' => $this->game_account_id,
            'game_server_id' => $this->game_server_id,
            'status' => $this->status,
            'created_at' => $this->created_at,
        ];
    }
}
