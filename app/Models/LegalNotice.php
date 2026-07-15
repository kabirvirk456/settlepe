<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['lead_user_id', 'created_by_user_id', 'lender_name', 'notice_type', 'received_at', 'response_due_at', 'priority', 'status', 'customer_instructions', 'original_name', 'path', 'mime_type', 'size'])]
class LegalNotice extends Model
{
    public const PRIORITIES = ['normal' => 'Normal', 'high' => 'High', 'urgent' => 'Urgent'];

    public const STATUSES = ['received' => 'Received', 'under_review' => 'Under review', 'response_drafted' => 'Response drafted', 'responded' => 'Responded', 'closed' => 'Closed'];

    protected function casts(): array
    {
        return ['received_at' => 'date', 'response_due_at' => 'date'];
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(User::class, 'lead_user_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }
}
