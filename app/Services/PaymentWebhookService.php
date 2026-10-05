<?php

namespace App\Services;

use App\Jobs\ProcessTopUp;
use App\Models\Payment;
use App\Models\PaymentWebhookLog;

class PaymentWebhookService
{
    public function isValidSignature(array $payload): bool
    {
        if (! isset($payload['order_id'], $payload['status_code'], $payload['gross_amount'], $payload['signature_key'])) {
            return false;
        }

        $serverKey = config('services.midtrans.server_key');

        $expectedSignature = hash('sha512',
            $payload['order_id'] .
            $payload['status_code'] .
            $payload['gross_amount'] .
            $serverKey
        );

        return hash_equals($expectedSignature, $payload['signature_key']);
    }

    public function process(array $payload, PaymentWebhookLog $log): void
    {
        $payment = Payment::where('midtrans_order_id', $payload['order_id'])->first();

        if (! $payment) {
            return;
        }

        if ($payment->status === 'settlement') {
            $log->update(['processed' => true, 'processed_at' => now()]);
            return;
        }

        $transactionStatus = $payload['transaction_status'];

        $payment->update([
            'status' => $transactionStatus,
            'midtrans_transaction_id' => $payload['transaction_id'] ?? null,
            'payment_method' => $payload['payment_type'] ?? null,
            'paid_at' => in_array($transactionStatus, ['settlement', 'capture']) ? now() : null,
        ]);

        $order = $payment->order;

        if (in_array($transactionStatus, ['settlement', 'capture'])) {
            $order->update(['status' => 'paid']);
            ProcessTopUp::dispatch($order);
        } elseif (in_array($transactionStatus, ['expire', 'cancel', 'deny'])) {
            $order->update(['status' => $transactionStatus === 'expire' ? 'expired' : 'failed']);
        }

        $log->update(['processed' => true, 'processed_at' => now()]);
    }
}