<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use Midtrans\Config;
use Midtrans\Snap;

class PaymentService
{
    public function __construct()
    {
        Config::$serverKey = config('services.midtrans.server_key');
        Config::$isProduction = config('services.midtrans.is_production');
        Config::$isSanitized = true;
        Config::$is3ds = true;
    }

    public function createPayment(Order $order): array
    {
        $params = [
            'transaction_details' => [
                'order_id' => $order->order_number,
                'gross_amount' => (int) $order->price_snapshot,
            ],
            'customer_details' => [
                'first_name' => $order->user->name,
                'email' => $order->user->email,
            ],
            'item_details' => [[
                'id' => (string) $order->product_id,
                'price' => (int) $order->price_snapshot,
                'quantity' => 1,
                'name' => $order->product_name_snapshot,
            ]],
        ];

        $snapToken = Snap::getSnapToken($params);

        $payment = Payment::create([
            'order_id' => $order->id,
            'midtrans_order_id' => $order->order_number,
            'amount' => $order->price_snapshot,
            'status' => 'pending',
        ]);

        return [
            'payment' => $payment,
            'snap_token' => $snapToken,
        ];
    }
}