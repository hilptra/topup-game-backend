<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PaymentWebhookLog;
use App\Services\PaymentWebhookService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentWebhookController extends Controller
{
    public function __construct(
        private readonly PaymentWebhookService $webhookService
    ) {}

    public function handle(Request $request): JsonResponse
    {
        $payload = $request->all();

        $log = PaymentWebhookLog::create([
            'source' => 'midtrans',
            'payload' => $payload,
            'signature_valid' => false,
            'processed' => false,
        ]);

        if (! $this->webhookService->isValidSignature($payload)) {
            return response()->json(['message' => 'Invalid signature.'], 403);
        }

        $log->update(['signature_valid' => true]);

        $this->webhookService->process($payload, $log);

        return response()->json(['message' => 'Webhook processed.']);
    }
}