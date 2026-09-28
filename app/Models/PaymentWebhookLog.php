<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentWebhookLog extends Model
{
    use HasFactory;

    const UPDATED_AT = null;

    protected $fillable = [
        'source',
        'payload',
        'signature_valid',
        'processed',
        'processed_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'signature_valid' => 'boolean',
        'processed' => 'boolean',
        'processed_at' => 'datetime',
    ];
}