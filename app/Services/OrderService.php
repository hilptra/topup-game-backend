<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Str;

class OrderService {

    public function validateGameAccount(string $gameAccountId, ?string $gameServerId): array
    {
        // Validasi dummy untuk MVP. Nanti bagian ini yang diganti
        // dengan pemanggilan API provider asli (mis. cek ke Digiflazz).
        return [
            'valid' => true,
            'username' => 'Player_' . substr($gameAccountId, -4),
        ];
    }

    public function createOrder(User $user, Product $product, string $gameAccountId, ?string $gameServerId): Order
    {
        return Order::create([
            'order_number' => $this->generateOrderNumber(),
            'user_id' => $user->id,
            'product_id' => $product->id,
            'game_account_id' => $gameAccountId,
            'game_server_id' => $gameServerId,
            'product_name_snapshot' => $product->name,
            'price_snapshot' => $product->price,
            'status' => 'pending',
        ]);
    }

    private function generateOrderNumber(): string
    {
        return 'ORD-' . now()->format('Ymd') . '-' . strtoupper(Str::random(6));
    }
}