<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable([
    'name',
    'first_name',
    'middle_name',
    'last_name',
    'email',
    'password',
    'mobile',
    'age',
    'gender',
    'pan_card',
    'income',
    'dob',
    'address',
    'village',
    'city',
    'state',
    'pincode',
    'cibil_profile_completed_at',
    'incomplete_application_reminded_at',
    'terms_accepted_at',
    'terms_version',
    'terms_accepted_ip',
    'role',
    'assigned_sales_id',
    'assigned_rm_id',
    'sales_status',
    'sales_notes',
    'last_sales_contacted_at',
    'follow_up_at',
    'priority',
    'call_disposition',
    'consultation_fee_paid_at',
    'service_fee_paid_at',
    'rm_status',
    'case_stage',
    'case_reference',
    'rm_notes',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const ROLE_CUSTOMER = 'customer';

    public const ROLE_SALES = 'sales';

    public const ROLE_RM = 'rm';

    public const ROLE_ADMIN = 'admin';

    public const SALES_STATUSES = [
        'cibil_fetched' => 'CRIF report fetched',
        'consultation_paid' => 'Rs. 99 paid',
        'call_connected' => 'Call connected',
        'settlement_pitched' => 'Settlement pitched',
        'follow_up' => 'Follow up',
        'no_reply' => 'No reply',
        'not_interested' => 'Not interested',
        'service_fee_paid' => 'Settlement service activated',
    ];

    public const CALL_DISPOSITIONS = [
        'not_connected' => 'Not connected',
        'interested' => 'Interested',
        'callback' => 'Callback',
        'not_interested' => 'Not interested',
        'converted' => 'Converted',
        'wrong_number' => 'Wrong number',
    ];

    public const PRIORITIES = [
        'low' => 'Low',
        'normal' => 'Normal',
        'high' => 'High',
        'urgent' => 'Urgent',
    ];

    public const RM_STATUSES = [
        'work_started' => 'Work started',
        'settlement_offered' => 'Settlement offered',
        'case_closed_settle' => 'Case closed (settle)',
        'case_closed_unsettle' => 'Case closed (unsettle)',
    ];

    public const CASE_STAGES = [
        'enrolled' => 'Enrollment completed',
        'documents_pending' => 'Documents pending',
        'documents_review' => 'Documents under review',
        'assessment_completed' => 'Financial assessment completed',
        'lender_contacted' => 'Lender communication initiated',
        'negotiation' => 'Settlement negotiation underway',
        'offer_received' => 'Offer received',
        'customer_approval' => 'Customer approval pending',
        'payment' => 'Settlement payment in progress',
        'settlement_letter' => 'Settlement letter received',
        'closure_pending' => 'NOC / closure pending',
        'completed' => 'Case completed',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'cibil_profile_completed_at' => 'datetime',
            'incomplete_application_reminded_at' => 'datetime',
            'terms_accepted_at' => 'datetime',
            'last_sales_contacted_at' => 'datetime',
            'follow_up_at' => 'datetime',
            'consultation_fee_paid_at' => 'datetime',
            'service_fee_paid_at' => 'datetime',
            'email_verified_at' => 'datetime',
            'income' => 'integer',
            'dob' => 'date',
            'password' => 'hashed',
        ];
    }

    public function isCustomer(): bool
    {
        return $this->role === self::ROLE_CUSTOMER;
    }

    public function crifReports(): HasMany
    {
        return $this->hasMany(CrifReport::class);
    }

    public function latestCompletedCrifReport(): HasOne
    {
        return $this->hasOne(CrifReport::class)
            ->where('status', 'completed')
            ->latestOfMany('completed_at');
    }

    public function consultationPayments(): HasMany
    {
        return $this->hasMany(ConsultationPayment::class);
    }

    public function isSales(): bool
    {
        return $this->role === self::ROLE_SALES;
    }

    public function isRm(): bool
    {
        return $this->role === self::ROLE_RM;
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isTeamMember(): bool
    {
        return in_array($this->role, [self::ROLE_SALES, self::ROLE_RM, self::ROLE_ADMIN], true);
    }

    public function canAccessSalesCrm(): bool
    {
        return $this->isSales() || $this->isAdmin();
    }

    public function canAccessRmCrm(): bool
    {
        return $this->isRm() || $this->isAdmin();
    }

    public function assignedSales(): BelongsTo
    {
        return $this->belongsTo(self::class, 'assigned_sales_id');
    }

    public function assignedRm(): BelongsTo
    {
        return $this->belongsTo(self::class, 'assigned_rm_id');
    }

    public function salesLeads(): HasMany
    {
        return $this->hasMany(self::class, 'assigned_sales_id');
    }

    public function rmLeadsAssigned(): HasMany
    {
        return $this->hasMany(self::class, 'assigned_rm_id');
    }

    public function leadActivities(): HasMany
    {
        return $this->hasMany(LeadActivity::class, 'lead_user_id')->latest();
    }

    public function leadDocuments(): HasMany
    {
        return $this->hasMany(LeadDocument::class, 'lead_user_id')->latest();
    }

    public function settlementAccounts(): HasMany
    {
        return $this->hasMany(SettlementAccount::class, 'lead_user_id')->latest();
    }

    public function customerTasks(): HasMany
    {
        return $this->hasMany(CustomerTask::class, 'lead_user_id')->latest();
    }

    public function legalNotices(): HasMany
    {
        return $this->hasMany(LegalNotice::class, 'lead_user_id')->latest('received_at');
    }

    public function scopeCustomerLeads(Builder $query): Builder
    {
        return $query
            ->where('role', self::ROLE_CUSTOMER)
            ->where(function (Builder $query) {
                $query
                    ->whereNotNull('cibil_profile_completed_at')
                    ->orWhereNotNull('consultation_fee_paid_at');
            });
    }

    public function scopeRmLeads(Builder $query): Builder
    {
        return $query
            ->where('role', self::ROLE_CUSTOMER)
            ->whereNotNull('service_fee_paid_at');
    }

    public function scopeTeamMembers(Builder $query): Builder
    {
        return $query->whereIn('role', [self::ROLE_SALES, self::ROLE_RM, self::ROLE_ADMIN]);
    }
}
