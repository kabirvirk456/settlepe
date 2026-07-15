<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'lead_user_id',
    'uploaded_by_user_id',
    'document_type',
    'review_status',
    'review_notes',
    'customer_visible',
    'original_name',
    'path',
    'mime_type',
    'size',
])]
class LeadDocument extends Model
{
    public const REVIEW_STATUSES = ['pending' => 'Under review', 'accepted' => 'Accepted', 'rejected' => 'Needs replacement'];

    public const TYPES = [
        'pan' => 'PAN card',
        'aadhaar' => 'Aadhaar',
        'loan_statement' => 'Loan statement',
        'bank_notice' => 'Bank notice',
        'settlement_letter' => 'Settlement letter',
        'closure_letter' => 'Closure letter',
        'other' => 'Other',
    ];

    protected function casts(): array
    {
        return ['customer_visible' => 'boolean'];
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(User::class, 'lead_user_id');
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by_user_id');
    }
}
