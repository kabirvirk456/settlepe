<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConsultationPayment extends Model
{
    protected $fillable = [
        'provider',
        'amount',
        'currency',
        'receipt',
        'order_id',
        'payment_id',
        'status',
        'failure_reason',
        'provider_response',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'provider_response' => 'encrypted:array',
            'paid_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
