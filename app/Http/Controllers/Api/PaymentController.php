<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePaymentRequest;
use App\Http\Resources\PaymentResource;
use App\Models\Order;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(
        private readonly PaymentService $paymentService
    ) {}

    public function store(StorePaymentRequest $request): JsonResponse 
    {
        $order = Order::findOrFail($request->order_id);

        $result = $this->paymentService->createPayment($order);

        return response()->json([
            'data' => [
                'payment' => new PaymentResource($result['payment']),
                'snap_token' => $result['snap_token'],
            ],
            'message' => 'Pembayaran berhasil dibuat',
        ], 201);
    }
}
