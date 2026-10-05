<?php

namespace App\Services\TopUp;

use App\Models\Order;

class MockTopUpProvider implements TopUpProviderInterface
{
    public function process(Order $order): array
    {
        // Simulasi delay seolah-olah manggil API provider asli
        sleep(1);

        $outcomes = ['success', 'success', 'success', 'pending', 'failed'];
        $result = $outcomes[array_rand($outcomes)];

        return [
            'status' => $result,
            'provider_reference_id' => 'MOCK-' . strtoupper(uniqid()),
            'response_payload' => [
                'provider' => 'mock',
                'processed_at' => now()->toIso8601String(),
                'message' => match ($result) {
                    'success' => 'Top up berhasil diproses.',
                    'pending' => 'Top up sedang diproses oleh provider.',
                    'failed' => 'Top up gagal, saldo provider tidak mencukupi.',
                },
            ],
        ];
    }
}