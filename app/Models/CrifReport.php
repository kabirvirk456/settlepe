<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CrifReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'report_id',
        'order_id',
        'status',
        'authentication_prompt',
        'initial_response',
        'report_response',
        'error_message',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'initial_response' => 'encrypted:array',
            'report_response' => 'encrypted:array',
            'completed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
