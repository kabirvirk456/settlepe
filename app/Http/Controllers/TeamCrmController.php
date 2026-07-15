<?php

namespace App\Http\Controllers;

use App\Models\CustomerTask;
use App\Models\LeadActivity;
use App\Models\LeadDocument;
use App\Models\LegalNotice;
use App\Models\SettlementAccount;
use App\Models\User;
use App\Services\CrifReportSummaryService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TeamCrmController extends Controller
{
    public function __construct(private readonly CrifReportSummaryService $crifReportSummary) {}

    public function home(Request $request): RedirectResponse
    {
        $teamUser = $request->user();

        abort_unless($teamUser?->isTeamMember(), 403);

        if ($teamUser->isRm()) {
            return redirect()->route('team.rm');
        }

        if ($teamUser->isAdmin()) {
            return redirect()->route('team.admin');
        }

        return redirect()->route('team.sales');
    }

    public function sales(Request $request): View
    {
        $teamUser = $request->user();

        abort_unless($teamUser?->canAccessSalesCrm(), 403);

        $baseQuery = $this->visibleSalesLeads($teamUser);
        $leads = $this->salesQuery($request, clone $baseQuery)
            ->paginate(12)
            ->withQueryString();
        $unpaidLeads = (clone $baseQuery)
            ->whereNull('consultation_fee_paid_at')
            ->whereNull('service_fee_paid_at')
            ->with(['assignedSales', 'assignedRm'])
            ->latest('cibil_profile_completed_at')
            ->limit(8)
            ->get();
        $paidLeads = (clone $baseQuery)
            ->whereNotNull('consultation_fee_paid_at')
            ->whereNull('service_fee_paid_at')
            ->with(['assignedSales', 'assignedRm'])
            ->latest('consultation_fee_paid_at')
            ->limit(10)
            ->get();
        $convertedLeads = (clone $baseQuery)
            ->whereNotNull('service_fee_paid_at')
            ->with(['assignedSales', 'assignedRm'])
            ->latest('service_fee_paid_at')
            ->limit(8)
            ->get();

        $summaryLeads = collect($leads->items())
            ->concat($unpaidLeads)
            ->concat($paidLeads)
            ->concat($convertedLeads)
            ->unique('id');

        return view('team.sales', [
            'leads' => $leads,
            'unpaidLeads' => $unpaidLeads,
            'paidLeads' => $paidLeads,
            'convertedLeads' => $convertedLeads,
            'creditSummaries' => $this->creditSummaries($summaryLeads),
            'metrics' => [
                'total' => (clone $baseQuery)->count(),
                'notPaid99' => (clone $baseQuery)->whereNull('consultation_fee_paid_at')->count(),
                'paid99' => (clone $baseQuery)->whereNotNull('consultation_fee_paid_at')->count(),
                'paidPendingRecovery' => (clone $baseQuery)
                    ->whereNotNull('consultation_fee_paid_at')
                    ->whereNull('service_fee_paid_at')
                    ->count(),
                'uncontacted' => (clone $baseQuery)->whereNull('last_sales_contacted_at')->count(),
                'pitched' => (clone $baseQuery)->where('sales_status', 'settlement_pitched')->count(),
                'serviceActivated' => (clone $baseQuery)->whereNotNull('service_fee_paid_at')->count(),
                'todayFollowUps' => (clone $baseQuery)
                    ->whereNotNull('follow_up_at')
                    ->whereDate('follow_up_at', '<=', now()->toDateString())
                    ->count(),
            ],
            'dueFollowUps' => (clone $baseQuery)
                ->whereNotNull('follow_up_at')
                ->whereDate('follow_up_at', '<=', now()->toDateString())
                ->orderBy('follow_up_at')
                ->limit(6)
                ->get(),
            'preConsultationLeads' => $unpaidLeads,
            'stageCounts' => [
                'cibil_fetched' => (clone $baseQuery)->where('sales_status', 'cibil_fetched')->count(),
                'consultation_paid' => (clone $baseQuery)->where('sales_status', 'consultation_paid')->count(),
                'call_connected' => (clone $baseQuery)->where('sales_status', 'call_connected')->count(),
                'settlement_pitched' => (clone $baseQuery)->where('sales_status', 'settlement_pitched')->count(),
                'follow_up' => (clone $baseQuery)->where('sales_status', 'follow_up')->count(),
                'no_reply' => (clone $baseQuery)->where('sales_status', 'no_reply')->count(),
                'service_fee_paid' => (clone $baseQuery)->where('sales_status', 'service_fee_paid')->count(),
            ],
            'salesStatuses' => User::SALES_STATUSES,
            'callDispositions' => User::CALL_DISPOSITIONS,
            'priorities' => User::PRIORITIES,
            'selectedStatus' => $request->query('status'),
            'selectedPriority' => $request->query('priority'),
            'dueOnly' => $request->boolean('due'),
            'search' => $request->query('search'),
        ]);
    }

    public function rm(Request $request): View
    {
        $teamUser = $request->user();

        abort_unless($teamUser?->canAccessRmCrm(), 403);

        $baseQuery = $this->visibleRmLeads($teamUser);
        $query = (clone $baseQuery)->latest('service_fee_paid_at');

        if ($request->query('status')) {
            $query->where('rm_status', $request->query('status'));
        }

        if ($request->query('search')) {
            $this->applySearch($query, $request->query('search'));
        }

        $leads = $query->paginate(12)->withQueryString();

        return view('team.rm', [
            'leads' => $leads,
            'creditSummaries' => $this->creditSummaries(collect($leads->items())),
            'metrics' => [
                'total' => (clone $baseQuery)->count(),
                'work_started' => (clone $baseQuery)->where('rm_status', 'work_started')->count(),
                'settlement_offered' => (clone $baseQuery)->where('rm_status', 'settlement_offered')->count(),
                'closed' => (clone $baseQuery)
                    ->whereIn('rm_status', ['case_closed_settle', 'case_closed_unsettle'])
                    ->count(),
            ],
            'rmStatuses' => User::RM_STATUSES,
            'selectedStatus' => $request->query('status'),
            'search' => $request->query('search'),
        ]);
    }

    public function admin(Request $request): View
    {
        $teamUser = $request->user();

        abort_unless($teamUser?->isAdmin(), 403);

        $baseQuery = User::customerLeads();
        $leads = $this->salesQuery($request, clone $baseQuery)
            ->paginate(12)
            ->withQueryString();
        $serviceActivated = User::customerLeads()->whereNotNull('service_fee_paid_at')->count();
        $settled = User::rmLeads()->where('rm_status', 'case_closed_settle')->count();
        $unsettled = User::rmLeads()->where('rm_status', 'case_closed_unsettle')->count();

        return view('team.admin', [
            'leads' => $leads,
            'metrics' => [
                'teamUsers' => User::whereIn('role', [User::ROLE_SALES, User::ROLE_RM, User::ROLE_ADMIN])->count(),
                'customerLeads' => User::customerLeads()->count(),
                'newToday' => User::customerLeads()->whereDate('cibil_profile_completed_at', now()->toDateString())->count(),
                'unassignedSales' => User::customerLeads()->whereNull('assigned_sales_id')->count(),
                'unassignedRm' => User::rmLeads()->whereNull('assigned_rm_id')->count(),
                'paid99' => User::customerLeads()->whereNotNull('consultation_fee_paid_at')->count(),
                'serviceActivated' => $serviceActivated,
                'settled' => $settled,
                'unsettled' => $unsettled,
                'activeCases' => max(0, $serviceActivated - $settled - $unsettled),
                'todayFollowUps' => User::customerLeads()
                    ->whereNotNull('follow_up_at')
                    ->whereDate('follow_up_at', '<=', now()->toDateString())
                    ->count(),
            ],
            'salesReport' => $this->salesReport(),
            'rmReport' => $this->rmReport(),
            'followUps' => User::customerLeads()
                ->with(['assignedSales', 'assignedRm'])
                ->whereNotNull('follow_up_at')
                ->whereDate('follow_up_at', '<=', now()->toDateString())
                ->orderBy('follow_up_at')
                ->limit(8)
                ->get(),
            'recentActivities' => LeadActivity::with(['lead', 'actor'])->latest()->limit(6)->get(),
            'stageCounts' => $this->stageCounts(),
            'financials' => $this->financialSnapshot(),
            'salesStatuses' => User::SALES_STATUSES,
            'rmStatuses' => User::RM_STATUSES,
            'callDispositions' => User::CALL_DISPOSITIONS,
            'priorities' => User::PRIORITIES,
            'selectedStatus' => $request->query('status'),
            'search' => $request->query('search'),
        ]);
    }

    public function show(Request $request, User $lead): View
    {
        $this->authorizeLeadAccess($request->user(), $lead);
        $lead->load([
            'assignedSales',
            'assignedRm',
            'leadActivities.actor',
            'leadDocuments.uploadedBy',
            'settlementAccounts',
            'customerTasks.createdBy',
            'legalNotices.createdBy',
            'latestCompletedCrifReport',
        ]);

        $creditReport = $this->creditReport($lead);

        return view('team.lead-show', [
            'lead' => $lead,
            ...$creditReport,
            'salesStatuses' => User::SALES_STATUSES,
            'rmStatuses' => User::RM_STATUSES,
            'callDispositions' => User::CALL_DISPOSITIONS,
            'priorities' => User::PRIORITIES,
            'salesUsers' => User::where('role', User::ROLE_SALES)->orderBy('name')->get(),
            'rmUsers' => User::where('role', User::ROLE_RM)->orderBy('name')->get(),
            'documentTypes' => LeadDocument::TYPES,
            'closureStatuses' => SettlementAccount::CLOSURE_STATUSES,
            'settlementStages' => SettlementAccount::STAGES,
            'caseStages' => User::CASE_STAGES,
            'taskPriorities' => CustomerTask::PRIORITIES,
            'noticePriorities' => LegalNotice::PRIORITIES,
            'noticeStatuses' => LegalNotice::STATUSES,
            'whatsappTemplates' => $this->whatsappTemplates($lead),
        ]);
    }

    public function updateSales(Request $request, User $lead): RedirectResponse
    {
        $this->authorizeLeadAccess($request->user(), $lead, 'sales');

        $validated = $request->validate([
            'sales_status' => ['required', Rule::in(array_keys(User::SALES_STATUSES))],
            'call_disposition' => ['nullable', Rule::in(array_keys(User::CALL_DISPOSITIONS))],
            'priority' => ['required', Rule::in(array_keys(User::PRIORITIES))],
            'follow_up_at' => ['nullable', 'date'],
            'sales_notes' => ['nullable', 'string', 'max:3000'],
            'mark_service_fee_paid' => ['nullable', 'boolean'],
        ]);

        $previousStatus = $lead->sales_status;

        $updates = [
            'sales_status' => $validated['sales_status'],
            'call_disposition' => $validated['call_disposition'] ?? ($validated['sales_status'] === 'no_reply' ? 'not_connected' : null),
            'priority' => $validated['priority'],
            'follow_up_at' => $validated['follow_up_at'] ?? null,
            'sales_notes' => $validated['sales_notes'] ?? null,
            'last_sales_contacted_at' => now(),
        ];

        if ($request->boolean('mark_service_fee_paid') || $validated['sales_status'] === 'service_fee_paid') {
            $updates['sales_status'] = 'service_fee_paid';
            $updates['service_fee_paid_at'] = $lead->service_fee_paid_at ?: now();
            $updates['rm_status'] = $lead->rm_status ?: 'work_started';
            $updates['case_stage'] = $lead->case_stage ?: 'enrolled';
            $updates['case_reference'] = $lead->case_reference ?: 'SP-'.now()->format('ym').'-'.str_pad((string) $lead->id, 6, '0', STR_PAD_LEFT);

            if (! $lead->assigned_rm_id && $rm = $this->nextAvailableRm()) {
                $updates['assigned_rm_id'] = $rm->id;
            }
        }

        $lead->update($updates);
        $this->recordActivity($lead, $request->user(), 'Sales status updated', $validated['sales_notes'] ?? null, [
            'from' => $previousStatus,
            'to' => $lead->sales_status,
            'call_disposition' => $lead->call_disposition,
            'follow_up_at' => optional($lead->follow_up_at)->toDateTimeString(),
        ]);

        return back()->with('status', 'Sales lead updated.');
    }

    public function updateRm(Request $request, User $lead): RedirectResponse
    {
        $this->authorizeLeadAccess($request->user(), $lead, 'rm');

        $validated = $request->validate([
            'rm_status' => ['required', Rule::in(array_keys(User::RM_STATUSES))],
            'rm_notes' => ['nullable', 'string', 'max:3000'],
        ]);

        $previousStatus = $lead->rm_status;
        $lead->update($validated);
        $this->recordActivity($lead, $request->user(), 'RM status updated', $validated['rm_notes'] ?? null, [
            'from' => $previousStatus,
            'to' => $lead->rm_status,
        ]);

        return back()->with('status', 'RM lead updated.');
    }

    public function updateAssignment(Request $request, User $lead): RedirectResponse
    {
        abort_unless($request->user()?->isAdmin(), 403);
        abort_unless($lead->isCustomer(), 404);

        $validated = $request->validate([
            'assigned_sales_id' => ['nullable', Rule::exists('users', 'id')->where('role', User::ROLE_SALES)],
            'assigned_rm_id' => ['nullable', Rule::exists('users', 'id')->where('role', User::ROLE_RM)],
        ]);

        $lead->update($validated);
        $lead->refresh()->load(['assignedSales', 'assignedRm']);

        $this->recordActivity($lead, $request->user(), 'Assignment updated', sprintf(
            'Sales: %s. RM: %s.',
            $lead->assignedSales?->name ?? 'Unassigned',
            $lead->assignedRm?->name ?? 'Unassigned',
        ));

        return back()->with('status', 'Lead assignment updated.');
    }

    public function storeActivity(Request $request, User $lead): RedirectResponse
    {
        $this->authorizeLeadAccess($request->user(), $lead);

        $validated = $request->validate([
            'notes' => ['required', 'string', 'max:3000'],
            'customer_visible' => ['nullable', 'boolean'],
        ]);

        $this->recordActivity($lead, $request->user(), $request->boolean('customer_visible') ? 'Case update' : 'Manual note', $validated['notes'], [], $request->boolean('customer_visible'));

        return back()->with('status', 'Activity note added.');
    }

    public function updateCase(Request $request, User $lead): RedirectResponse
    {
        $this->authorizeLeadAccess($request->user(), $lead, 'rm');
        abort_unless($lead->service_fee_paid_at, 422);
        $validated = $request->validate(['case_stage' => ['required', Rule::in(array_keys(User::CASE_STAGES))]]);
        $previous = $lead->case_stage ?: 'enrolled';
        $lead->update([
            'case_stage' => $validated['case_stage'],
            'case_reference' => $lead->case_reference ?: 'SP-'.now()->format('ym').'-'.str_pad((string) $lead->id, 6, '0', STR_PAD_LEFT),
        ]);
        $this->recordActivity($lead, $request->user(), 'Case stage updated', User::CASE_STAGES[$validated['case_stage']], ['from' => $previous, 'to' => $validated['case_stage']], true);

        return back()->with('status', 'Customer case stage updated.');
    }

    public function storeTask(Request $request, User $lead): RedirectResponse
    {
        $this->authorizeLeadAccess($request->user(), $lead, 'rm');
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'priority' => ['required', Rule::in(array_keys(CustomerTask::PRIORITIES))],
            'due_at' => ['nullable', 'date'],
        ]);
        $task = $lead->customerTasks()->create([...$validated, 'created_by_user_id' => $request->user()->id, 'status' => 'pending']);
        $this->recordActivity($lead, $request->user(), 'Action required', $task->title, ['task_id' => $task->id], true);

        return back()->with('status', 'Customer action created.');
    }

    public function storeLegalNotice(Request $request, User $lead): RedirectResponse
    {
        $this->authorizeLeadAccess($request->user(), $lead, 'rm');
        $validated = $request->validate([
            'lender_name' => ['required', 'string', 'max:255'],
            'notice_type' => ['required', 'string', 'max:255'],
            'received_at' => ['required', 'date'],
            'response_due_at' => ['nullable', 'date', 'after_or_equal:received_at'],
            'priority' => ['required', Rule::in(array_keys(LegalNotice::PRIORITIES))],
            'status' => ['required', Rule::in(array_keys(LegalNotice::STATUSES))],
            'customer_instructions' => ['nullable', 'string', 'max:3000'],
            'notice' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:5120'],
        ]);
        $fileFields = [];
        if ($file = $request->file('notice')) {
            $fileName = uniqid('', true).'-'.preg_replace('/[^A-Za-z0-9._-]/', '-', $file->getClientOriginalName());
            $fileFields = ['original_name' => $file->getClientOriginalName(), 'path' => $file->storeAs('legal-notices/'.$lead->id, $fileName), 'mime_type' => $file->getClientMimeType(), 'size' => $file->getSize() ?: 0];
        }
        unset($validated['notice']);
        $notice = $lead->legalNotices()->create([...$validated, ...$fileFields, 'created_by_user_id' => $request->user()->id]);
        $this->recordActivity($lead, $request->user(), 'Legal notice added', $notice->lender_name.' · '.$notice->notice_type, ['notice_id' => $notice->id], true);

        return back()->with('status', 'Legal notice added to the customer portal.');
    }

    public function reviewDocument(Request $request, LeadDocument $document): RedirectResponse
    {
        $this->authorizeLeadAccess($request->user(), $document->lead, 'rm');
        $validated = $request->validate([
            'review_status' => ['required', Rule::in(array_keys(LeadDocument::REVIEW_STATUSES))],
            'review_notes' => ['nullable', 'string', 'max:1000'],
        ]);
        $document->update($validated);
        $this->recordActivity($document->lead, $request->user(), 'Document review updated', (LeadDocument::TYPES[$document->document_type] ?? 'Document').' · '.LeadDocument::REVIEW_STATUSES[$validated['review_status']], ['document_id' => $document->id], true);

        return back()->with('status', 'Document review updated.');
    }

    public function storeDocument(Request $request, User $lead): RedirectResponse
    {
        $this->authorizeLeadAccess($request->user(), $lead);

        $validated = $request->validate([
            'document_type' => ['required', Rule::in(array_keys(LeadDocument::TYPES))],
            'document' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $file = $validated['document'];
        $fileName = uniqid('', true).'-'.preg_replace('/[^A-Za-z0-9._-]/', '-', $file->getClientOriginalName());
        $path = $file->storeAs('crm-documents/'.$lead->id, $fileName);

        $document = $lead->leadDocuments()->create([
            'uploaded_by_user_id' => $request->user()->id,
            'document_type' => $validated['document_type'],
            'original_name' => $file->getClientOriginalName(),
            'path' => $path,
            'mime_type' => $file->getClientMimeType(),
            'size' => $file->getSize() ?: 0,
        ]);

        $this->recordActivity($lead, $request->user(), 'Document uploaded', LeadDocument::TYPES[$document->document_type].': '.$document->original_name);

        return back()->with('status', 'Document uploaded.');
    }

    public function downloadDocument(Request $request, LeadDocument $document)
    {
        $this->authorizeLeadAccess($request->user(), $document->lead);

        return Storage::download($document->path, $document->original_name);
    }

    public function storeSettlementAccount(Request $request, User $lead): RedirectResponse
    {
        $this->authorizeLeadAccess($request->user(), $lead, 'rm');

        $validated = $this->validatedSettlementAccount($request);

        $account = $lead->settlementAccounts()->create($validated);

        $this->recordActivity($lead, $request->user(), 'Settlement account added', $account->bank_name.' added to settlement tracker.');

        return back()->with('status', 'Settlement account added.');
    }

    public function updateSettlementAccount(Request $request, SettlementAccount $settlementAccount): RedirectResponse
    {
        $this->authorizeLeadAccess($request->user(), $settlementAccount->lead, 'rm');

        $validated = $this->validatedSettlementAccount($request);

        $settlementAccount->update($validated);

        $this->recordActivity($settlementAccount->lead, $request->user(), 'Settlement tracker updated', $settlementAccount->bank_name.' tracker updated.');

        return back()->with('status', 'Settlement tracker updated.');
    }

    private function salesQuery(Request $request, Builder $query): Builder
    {
        $query->with(['assignedSales', 'assignedRm', 'latestCompletedCrifReport'])->latest('cibil_profile_completed_at');

        if ($request->query('status')) {
            $query->where('sales_status', $request->query('status'));
        }

        if ($request->query('priority')) {
            $query->where('priority', $request->query('priority'));
        }

        if ($request->boolean('due')) {
            $query
                ->whereNotNull('follow_up_at')
                ->whereDate('follow_up_at', '<=', now()->toDateString());
        }

        if ($request->query('search')) {
            $this->applySearch($query, $request->query('search'));
        }

        return $query;
    }

    private function visibleSalesLeads(User $teamUser): Builder
    {
        $query = User::customerLeads();

        if ($teamUser->isSales()) {
            $query->where('assigned_sales_id', $teamUser->id);
        }

        return $query;
    }

    private function visibleRmLeads(User $teamUser): Builder
    {
        $query = User::rmLeads();

        if ($teamUser->isRm()) {
            $query->where('assigned_rm_id', $teamUser->id);
        }

        return $query;
    }

    private function nextAvailableRm(): ?User
    {
        return User::where('role', User::ROLE_RM)
            ->get()
            ->sortBy(fn (User $rm) => User::rmLeads()
                ->where('assigned_rm_id', $rm->id)
                ->whereIn('rm_status', ['work_started', 'settlement_offered'])
                ->count())
            ->first();
    }

    private function applySearch(Builder $query, string $search): void
    {
        $query->where(function (Builder $query) use ($search) {
            $query
                ->where('name', 'like', '%'.$search.'%')
                ->orWhere('mobile', 'like', '%'.$search.'%')
                ->orWhere('email', 'like', '%'.$search.'%')
                ->orWhere('pan_card', 'like', '%'.strtoupper($search).'%');
        });
    }

    private function authorizeLeadAccess(?User $teamUser, User $lead, ?string $area = null): void
    {
        abort_unless($teamUser?->isTeamMember(), 403);
        abort_unless($lead->isCustomer(), 404);

        if ($teamUser->isAdmin()) {
            return;
        }

        if ($area === 'sales') {
            abort_unless($teamUser->isSales() && $lead->assigned_sales_id === $teamUser->id, 403);

            return;
        }

        if ($area === 'rm') {
            abort_unless($teamUser->isRm() && $lead->service_fee_paid_at && $lead->assigned_rm_id === $teamUser->id, 403);

            return;
        }

        abort_unless(
            ($teamUser->isSales() && $lead->assigned_sales_id === $teamUser->id) ||
            ($teamUser->isRm() && $lead->service_fee_paid_at && $lead->assigned_rm_id === $teamUser->id),
            403,
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedSettlementAccount(Request $request): array
    {
        $validated = $request->validate([
            'bank_name' => ['required', 'string', 'max:255'],
            'product' => ['nullable', 'string', 'max:255'],
            'account_reference' => ['nullable', 'string', 'max:80'],
            'stage' => ['nullable', Rule::in(array_keys(SettlementAccount::STAGES))],
            'customer_visible' => ['nullable', 'boolean'],
            'outstanding_amount' => ['required', 'integer', 'min:0'],
            'offered_settlement_amount' => ['nullable', 'integer', 'min:0'],
            'final_settlement_amount' => ['nullable', 'integer', 'min:0'],
            'due_date' => ['nullable', 'date'],
            'closure_letter_status' => ['required', Rule::in(array_keys(SettlementAccount::CLOSURE_STATUSES))],
            'notes' => ['nullable', 'string', 'max:3000'],
        ]);

        $validated['stage'] = $validated['stage'] ?? 'assessment';
        $validated['customer_visible'] = $request->boolean('customer_visible');

        return $validated;
    }

    /**
     * @param  array<string, mixed>  $meta
     */
    private function recordActivity(User $lead, ?User $actor, string $event, ?string $notes = null, array $meta = [], bool $customerVisible = false): void
    {
        $lead->leadActivities()->create([
            'actor_user_id' => $actor?->id,
            'event' => $event,
            'notes' => $notes,
            'customer_visible' => $customerVisible,
            'meta' => $meta ?: null,
        ]);
    }

    /**
     * @return array<int, array{label: string, message: string}>
     */
    private function whatsappTemplates(User $lead): array
    {
        return [
            [
                'label' => 'Document request',
                'message' => 'Hi '.$lead->name.', please share your PAN, Aadhaar, latest loan statement, and any bank notice so our team can review your settlement case.',
            ],
            [
                'label' => 'Follow-up reminder',
                'message' => 'Hi '.$lead->name.', this is a quick follow-up from Settle Pe regarding your loan settlement discussion. Please let us know a convenient time to connect.',
            ],
            [
                'label' => 'Service activation',
                'message' => 'Hi '.$lead->name.', your settlement service has been activated. Our RM team will coordinate the next steps and document review.',
            ],
            [
                'label' => 'Settlement update',
                'message' => 'Hi '.$lead->name.', we have an update on your settlement process. Please connect with your RM to review the latest offer and next action.',
            ],
        ];
    }

    /**
     * @return array<int, array{name: string, assigned: int, service_activated: int, closed: int}>
     */
    private function salesReport(): array
    {
        return User::where('role', User::ROLE_SALES)
            ->orderBy('name')
            ->get()
            ->map(fn (User $sales) => [
                'name' => $sales->name,
                'assigned' => User::customerLeads()->where('assigned_sales_id', $sales->id)->count(),
                'service_activated' => User::customerLeads()
                    ->where('assigned_sales_id', $sales->id)
                    ->whereNotNull('service_fee_paid_at')
                    ->count(),
                'closed' => User::rmLeads()
                    ->where('assigned_sales_id', $sales->id)
                    ->whereIn('rm_status', ['case_closed_settle', 'case_closed_unsettle'])
                    ->count(),
            ])
            ->all();
    }

    /**
     * @return array<int, array{name: string, assigned: int, offered: int, closed: int}>
     */
    private function rmReport(): array
    {
        return User::where('role', User::ROLE_RM)
            ->orderBy('name')
            ->get()
            ->map(fn (User $rm) => [
                'name' => $rm->name,
                'assigned' => User::rmLeads()->where('assigned_rm_id', $rm->id)->count(),
                'offered' => User::rmLeads()
                    ->where('assigned_rm_id', $rm->id)
                    ->where('rm_status', 'settlement_offered')
                    ->count(),
                'closed' => User::rmLeads()
                    ->where('assigned_rm_id', $rm->id)
                    ->whereIn('rm_status', ['case_closed_settle', 'case_closed_unsettle'])
                    ->count(),
            ])
            ->all();
    }

    /**
     * @return array<int, array{label: string, count: int, color: string}>
     */
    private function stageCounts(): array
    {
        return [
            ['label' => 'Lead Created', 'count' => User::customerLeads()->where('sales_status', 'cibil_fetched')->count(), 'color' => '#4f67ff'],
            ['label' => 'Consultation Paid', 'count' => User::customerLeads()->whereNotNull('consultation_fee_paid_at')->count(), 'color' => '#49c7b1'],
            ['label' => 'Service Activated', 'count' => User::customerLeads()->whereNotNull('service_fee_paid_at')->count(), 'color' => '#f7b731'],
            ['label' => 'Active Case', 'count' => User::rmLeads()->whereIn('rm_status', ['work_started', 'settlement_offered'])->count(), 'color' => '#fb7185'],
            ['label' => 'Closed', 'count' => User::rmLeads()->whereIn('rm_status', ['case_closed_settle', 'case_closed_unsettle'])->count(), 'color' => '#8b5cf6'],
        ];
    }

    /**
     * @return array<string, int>
     */
    private function financialSnapshot(): array
    {
        $settlementAccounts = SettlementAccount::query()->get();
        $consultationFees = User::customerLeads()->whereNotNull('consultation_fee_paid_at')->count() * 99;
        $settlementPipeline = $settlementAccounts->sum(
            fn (SettlementAccount $account) => $account->final_settlement_amount
                ?? $account->offered_settlement_amount
                ?? 0
        );
        $outstandingUnderReview = $settlementAccounts->sum('outstanding_amount');

        return [
            'consultationFees' => $consultationFees,
            'settlementPipeline' => $settlementPipeline,
            'outstandingUnderReview' => $outstandingUnderReview,
            'documentsUploaded' => LeadDocument::query()->count(),
        ];
    }

    /** @return array<int, array{score: int|null, principal: int, remaining: int, highest_dpd: int}> */
    private function creditSummaries(iterable $leads): array
    {
        $summaries = [];

        foreach ($leads as $lead) {
            $report = $this->creditReport($lead);
            $summaries[$lead->id] = [
                'score' => $report['creditScore'],
                'principal' => $report['totalPrincipal'],
                'remaining' => $report['totalRemaining'],
                'highest_dpd' => $report['highestDpd'],
            ];
        }

        return $summaries;
    }

    /** @return array<string, mixed> */
    private function creditReport(User $lead): array
    {
        $storedReport = $lead->relationLoaded('latestCompletedCrifReport')
            ? $lead->latestCompletedCrifReport
            : $lead->latestCompletedCrifReport()->first();
        $summary = $this->crifReportSummary->summarize($storedReport?->report_response);
        $accounts = $summary['accounts'];

        return [
            'accounts' => $accounts,
            'creditScore' => $summary['score'],
            'totalPrincipal' => array_sum(array_column($accounts, 'principal_amount')),
            'totalRemaining' => array_sum(array_column($accounts, 'remaining_amount')),
            'highestDpd' => $accounts ? max(array_column($accounts, 'dpd')) : 0,
            'reportFetchedAt' => $storedReport?->completed_at,
        ];
    }
}
