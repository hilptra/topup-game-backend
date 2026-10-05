<?php

namespace App\Jobs;

use App\Models\Order;
use App\Models\TopUpTransaction;
use App\Services\TopUp\TopUpProviderInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessTopUp implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        public Order $order
    ) {}

    public function handle(TopUpProviderInterface $provider): void
    {
        $transaction = TopUpTransaction::firstOrCreate(
            ['order_id' => $this->order->id],
            ['provider' => 'mock', 'status' => 'pending']
        );

        $transaction->increment('attempt_count');

        $result = $provider->process($this->order);

        $transaction->update([
            'status' => $result['status'],
            'provider_reference_id' => $result['provider_reference_id'],
            'response_payload' => $result['response_payload'],
        ]);

        if ($result['status'] === 'success') {
            $this->order->update(['status' => 'success']);
        } elseif ($result['status'] === 'failed') {
            $this->order->update(['status' => 'failed']);
        }
        // kalau 'pending', order tetap 'paid', nunggu retry/proses manual
    }
}