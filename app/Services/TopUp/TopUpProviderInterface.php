<?php

namespace App\Services\TopUp;

use App\Models\Order;

interface TopUpProviderInterface
{
    public function process(Order $order): array;
}