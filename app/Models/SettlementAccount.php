<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'lead_user_id',
    'bank_name',
    'product',
    'account_reference',
    'stage',
    'customer_visible',
    'outstanding_amount',
    'offered_settlement_amount',
    'final_settlement_amount',
    'due_date',
    'closure_letter_status',
    'notes',
])]
class SettlementAccount extends Model
{
    public const STAGES = [
        'assessment' => 'Assessment', 'lender_contacted' => 'Lender contacted',
        'negotiation' => 'Negotiation', 'offer_received' => 'Offer received',
        'approved' => 'Customer approved', 'payment' => 'Payment in progress',
        'closed' => 'Closed',
    ];

    public const CLOSURE_STATUSES = [
        'pending' => 'Pending',
        'requested' => 'Requested',
        'received' => 'Received',
        'not_applicable' => 'Not applicable',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'customer_visible' => 'boolean',
        ];
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(User::class, 'lead_user_id');
    }
}
