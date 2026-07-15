<?php

namespace App\Http\Controllers;

use App\Models\CustomerTask;
use App\Models\LeadDocument;
use App\Models\LegalNotice;
use App\Models\User;
use App\Services\CrifReportSummaryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CustomerPortalController extends Controller
{
    public function dashboard(Request $request, CrifReportSummaryService $summaryService): View
    {
        $user = $this->enrolledCustomer($request);
        $this->ensureCaseIdentity($user);

        $user->load([
            'assignedRm',
            'settlementAccounts' => fn ($query) => $query->where('customer_visible', true),
            'leadDocuments' => fn ($query) => $query->where('customer_visible', true),
            'leadActivities' => fn ($query) => $query->where('customer_visible', true)->limit(20),
            'customerTasks',
            'legalNotices',
            'latestCompletedCrifReport',
        ]);

        $creditReport = $summaryService->summarize($user->latestCompletedCrifReport?->report_response);

        $accounts = $user->settlementAccounts;
        $totalOutstanding = $accounts->sum('outstanding_amount');
        $latestOffers = $accounts->sum(fn ($account) => $account->final_settlement_amount ?? $account->offered_settlement_amount ?? 0);
        $stageKeys = array_keys(User::CASE_STAGES);
        $stage = $user->case_stage ?: 'enrolled';
        $stageIndex = max(0, array_search($stage, $stageKeys, true) ?: 0);
        $progress = (int) round((($stageIndex + 1) / count($stageKeys)) * 100);

        return view('portal.dashboard', [
            'user' => $user,
            'accounts' => $accounts,
            'documents' => $user->leadDocuments,
            'activities' => $user->leadActivities,
            'tasks' => $user->customerTasks,
            'legalNotices' => $user->legalNotices,
            'documentTypes' => LeadDocument::TYPES,
            'caseStages' => User::CASE_STAGES,
            'currentStage' => $stage,
            'progress' => $progress,
            'totalOutstanding' => $totalOutstanding,
            'latestOffers' => $latestOffers,
            'estimatedSavings' => max(0, $totalOutstanding - $latestOffers),
            'creditScore' => $creditReport['score'],
            'bureauAccounts' => $creditReport['accounts'],
            'creditReportFetchedAt' => $user->latestCompletedCrifReport?->completed_at,
        ]);
    }

    public function storeDocument(Request $request): RedirectResponse
    {
        $user = $this->enrolledCustomer($request);
        $validated = $request->validate([
            'document_type' => ['required', Rule::in(array_keys(LeadDocument::TYPES))],
            'document' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $file = $validated['document'];
        $safeName = uniqid('', true).'-'.preg_replace('/[^A-Za-z0-9._-]/', '-', $file->getClientOriginalName());
        $path = $file->storeAs('crm-documents/'.$user->id, $safeName);

        $document = $user->leadDocuments()->create([
            'uploaded_by_user_id' => $user->id,
            'document_type' => $validated['document_type'],
            'review_status' => 'pending',
            'customer_visible' => true,
            'original_name' => $file->getClientOriginalName(),
            'path' => $path,
            'mime_type' => $file->getClientMimeType(),
            'size' => $file->getSize() ?: 0,
        ]);

        $user->leadActivities()->create([
            'actor_user_id' => $user->id,
            'event' => 'Document uploaded',
            'notes' => LeadDocument::TYPES[$document->document_type].' submitted for review.',
            'customer_visible' => true,
        ]);

        return back()->with('status', 'Document uploaded securely and sent for review.');
    }

    public function downloadDocument(Request $request, LeadDocument $document)
    {
        $user = $this->enrolledCustomer($request);
        abort_unless($document->lead_user_id === $user->id && $document->customer_visible, 404);

        return Storage::download($document->path, $document->original_name);
    }

    public function completeTask(Request $request, CustomerTask $task): RedirectResponse
    {
        $user = $this->enrolledCustomer($request);
        abort_unless($task->lead_user_id === $user->id, 404);

        if ($task->status !== 'completed') {
            $task->update(['status' => 'completed', 'completed_at' => now()]);
            $user->leadActivities()->create([
                'actor_user_id' => $user->id,
                'event' => 'Action completed',
                'notes' => $task->title,
                'customer_visible' => true,
            ]);
        }

        return back()->with('status', 'Action marked as completed.');
    }

    public function downloadNotice(Request $request, LegalNotice $notice)
    {
        $user = $this->enrolledCustomer($request);
        abort_unless($notice->lead_user_id === $user->id && $notice->path, 404);

        return Storage::download($notice->path, $notice->original_name);
    }

    private function enrolledCustomer(Request $request): User
    {
        $user = $request->user();
        abort_unless($user?->isCustomer(), 403);

        if (! $user->service_fee_paid_at) {
            abort(403, 'Your settlement service is not active yet.');
        }

        return $user;
    }

    private function ensureCaseIdentity(User $user): void
    {
        $updates = [];
        if (! $user->case_stage) {
            $updates['case_stage'] = 'enrolled';
        }
        if (! $user->case_reference) {
            $updates['case_reference'] = 'SP-'.now()->format('ym').'-'.str_pad((string) $user->id, 6, '0', STR_PAD_LEFT);
        }
        if ($updates) {
            $user->update($updates);
        }
    }
}
