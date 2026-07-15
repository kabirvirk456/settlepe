<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['lead_user_id', 'created_by_user_id', 'title', 'description', 'priority', 'status', 'due_at', 'completed_at'])]
class CustomerTask extends Model
{
    public const PRIORITIES = ['normal' => 'Normal', 'high' => 'High', 'urgent' => 'Urgent'];

    public const STATUSES = ['pending' => 'Pending', 'in_progress' => 'In progress', 'completed' => 'Completed'];

    protected function casts(): array
    {
        return ['due_at' => 'datetime', 'completed_at' => 'datetime'];
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
