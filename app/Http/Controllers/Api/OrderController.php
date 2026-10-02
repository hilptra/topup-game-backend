<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\ValidateGameAccountRequest;
use App\Http\Resources\OrderResource;
use App\Models\Game;
use App\Models\Order;
use App\Models\Product;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(
        private readonly OrderService $orderService
    ) {}

    public function validateAccount(ValidateGameAccountRequest $request, Game $game): JsonResponse
    {
        $result = $this->orderService->validateGameAccount(
            $request->game_account_id,
            $request->game_server_id
        );

        return response()->json(['data' => $result]);
    }

    public function store(StoreOrderRequest $request): JsonResponse
    {
        $product = Product::findOrFail($request->product_id);

        $order = $this->orderService->createOrder(
            user: $request->user(),
            product: $product,
            gameAccountId: $request->game_account_id,
            gameServerId: $request->game_server_id
        );

        return response()->json([
            'data' => new OrderResource($order),
            'message' => 'Order berhasil dibuat.',
        ], 201);
    }

    public function index(Request $request): JsonResponse
    {
        $orders = $request->user()
            ->orders()
            ->latest()
            ->get();

        return response()->json([
            'data' => OrderResource::collection($orders),
        ]);
    }

    public function show(Request $request, Order $order): JsonResponse
    {
        if ($order->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Tidak diizinkan.'], 403);
        }

        return response()->json([
            'data' => new OrderResource($order),
        ]);
    }
}