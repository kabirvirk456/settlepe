@extends('layouts.auth')

@section('title', 'My Settlement Case | Settle Pe')
@section('panel_width', 'w-full max-w-7xl')

@section('content')
@php
    $money = fn ($value) => 'Rs. '.number_format((int) $value);
    $pendingTasks = $tasks->where('status', '!=', 'completed');
    $urgentNotices = $legalNotices->filter(fn ($notice) => $notice->priority === 'urgent' && $notice->status !== 'closed');
    $stageKeys = array_keys($caseStages);
    $currentIndex = array_search($currentStage, $stageKeys, true);
@endphp

<section class="space-y-6">
    <div class="overflow-hidden rounded-2xl bg-[#10223f] text-white shadow-lg">
        <div class="grid gap-8 px-6 py-7 lg:grid-cols-[1fr_320px] lg:px-9 lg:py-9">
            <div>
                <div class="flex flex-wrap items-center gap-2 text-xs font-semibold uppercase tracking-wider">
                    <span class="rounded-full bg-[#f4c877] px-3 py-1 text-[#10223f]">Settlement service active</span>
                    <span class="rounded-full bg-white/10 px-3 py-1 text-white/75">Case {{ $user->case_reference }}</span>
                </div>
                <h1 class="mt-5 text-3xl font-semibold md:text-4xl">Welcome, {{ $user->name }}.</h1>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-white/70">Track lender negotiations, documents, legal notices and everything your case team needs from you.</p>
                <div class="mt-6">
                    <div class="flex items-center justify-between text-sm">
                        <span class="font-semibold">{{ $caseStages[$currentStage] }}</span>
                        <span class="text-white/65">{{ $progress }}% complete</span>
                    </div>
                    <div class="mt-2 h-2.5 overflow-hidden rounded-full bg-white/15"><div class="h-full rounded-full bg-[#f4c877]" style="width: {{ $progress }}%"></div></div>
                </div>
            </div>
            <div class="rounded-xl border border-white/15 bg-white/8 p-5">
                <p class="text-xs font-semibold uppercase tracking-wider text-white/55">Your relationship manager</p>
                <p class="mt-3 text-xl font-semibold">{{ $user->assignedRm?->name ?? 'Being assigned' }}</p>
                <p class="mt-2 text-sm leading-6 text-white/65">Your RM coordinates documents, lender communication and settlement next steps.</p>
                <a href="https://wa.me/91{{ preg_replace('/\D+/', '', (string) $user->mobile) }}" target="_blank" class="mt-4 inline-flex rounded-lg bg-white px-4 py-2 text-sm font-semibold text-[#10223f]">Contact support</a>
            </div>
        </div>
    </div>

    @if ($urgentNotices->isNotEmpty())
        <div class="rounded-xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-800">
            <strong>Urgent legal action:</strong> {{ $urgentNotices->first()->customer_instructions ?: 'A legal notice requires your attention. Review it below and contact your RM.' }}
        </div>
    @endif

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-xl border border-[#dfe5ec] bg-white p-5 shadow-sm"><p class="text-xs font-semibold uppercase tracking-wide text-[#6b7280]">Outstanding reviewed</p><p class="mt-3 text-2xl font-semibold">{{ $money($totalOutstanding) }}</p><p class="mt-1 text-sm text-[#6b7280]">Across {{ $accounts->count() }} lender accounts</p></div>
        <div class="rounded-xl border border-[#dfe5ec] bg-white p-5 shadow-sm"><p class="text-xs font-semibold uppercase tracking-wide text-[#6b7280]">Latest settlement value</p><p class="mt-3 text-2xl font-semibold">{{ $latestOffers ? $money($latestOffers) : 'Pending' }}</p><p class="mt-1 text-sm text-[#6b7280]">Confirmed and active offers</p></div>
        <div class="rounded-xl border border-[#dfe5ec] bg-white p-5 shadow-sm"><p class="text-xs font-semibold uppercase tracking-wide text-[#6b7280]">Potential savings</p><p class="mt-3 text-2xl font-semibold text-[#0d7a51]">{{ $latestOffers ? $money($estimatedSavings) : 'Under review' }}</p><p class="mt-1 text-sm text-[#6b7280]">Based on recorded offers</p></div>
        <div class="rounded-xl border border-[#dfe5ec] bg-white p-5 shadow-sm"><p class="text-xs font-semibold uppercase tracking-wide text-[#6b7280]">Action required</p><p class="mt-3 text-2xl font-semibold {{ $pendingTasks->count() ? 'text-[#b45309]' : 'text-[#0d7a51]' }}">{{ $pendingTasks->count() }}</p><p class="mt-1 text-sm text-[#6b7280]">{{ $pendingTasks->count() ? 'Customer tasks pending' : 'You are up to date' }}</p></div>
    </div>

    <div class="grid gap-6 xl:grid-cols-[1.45fr_.75fr]">
        <div class="space-y-6">
            <div class="rounded-xl border border-[#dfe5ec] bg-white p-5 shadow-sm">
                <div class="flex flex-col gap-3 border-b border-[#e7edf3] pb-5 md:flex-row md:items-center md:justify-between">
                    <div><h2 class="text-xl font-semibold">Credit and financial report</h2><p class="mt-1 text-sm text-[#6b7280]">Your verified profile and lender exposure used for settlement planning.</p></div>
                    <span class="w-fit rounded-full bg-[#eef8f3] px-3 py-1 text-xs font-semibold text-[#0d7a51]">Profile verified {{ $user->cibil_profile_completed_at?->format('d M Y') }}</span>
                </div>
                <div class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div><p class="text-xs font-semibold uppercase tracking-wide text-[#6b7280]">Applicant</p><p class="mt-2 font-semibold">{{ $user->name }}</p><p class="mt-1 text-sm text-[#6b7280]">Age {{ $user->age ?? '—' }}</p></div>
                    <div><p class="text-xs font-semibold uppercase tracking-wide text-[#6b7280]">PAN</p><p class="mt-2 font-semibold">{{ $user->pan_card ? substr($user->pan_card, 0, 2).'*****'.substr($user->pan_card, -3) : 'Not available' }}</p><p class="mt-1 text-sm text-[#6b7280]">Masked for security</p></div>
                    <div><p class="text-xs font-semibold uppercase tracking-wide text-[#6b7280]">Monthly income</p><p class="mt-2 font-semibold">{{ $user->income !== null ? $money($user->income) : 'Not available' }}</p><p class="mt-1 text-sm text-[#6b7280]">Customer reported</p></div>
                    <div><p class="text-xs font-semibold uppercase tracking-wide text-[#6b7280]">Accounts under review</p><p class="mt-2 font-semibold">{{ $accounts->count() }}</p><p class="mt-1 text-sm text-[#6b7280]">{{ $money($totalOutstanding) }} outstanding</p></div>
                </div>
                <div class="mt-5 grid gap-3 rounded-lg bg-[#f7f9fb] p-4 sm:grid-cols-3">
                    <div><p class="text-xs font-semibold uppercase text-[#6b7280]">CRIF score</p><p class="mt-1 text-lg font-semibold">{{ $creditScore ?? 'Not available' }}</p></div>
                    <div><p class="text-xs font-semibold uppercase text-[#6b7280]">Bureau accounts</p><p class="mt-1 text-lg font-semibold">{{ count($bureauAccounts) }}</p></div>
                    <div><p class="text-xs font-semibold uppercase text-[#6b7280]">Report fetched</p><p class="mt-1 text-sm font-semibold">{{ $creditReportFetchedAt?->format('d M Y') ?? 'Not available' }}</p></div>
                </div>
                <p class="mt-3 text-xs leading-5 text-[#6b7280]">CRIF values come from your completed bureau report. Settlement figures are maintained separately by your case team and are shown only after an offer is recorded.</p>
            </div>

            <div class="rounded-xl border border-[#dfe5ec] bg-white shadow-sm">
                <div class="border-b border-[#e7edf3] px-5 py-5"><h2 class="text-xl font-semibold">Settlement process</h2><p class="mt-1 text-sm text-[#6b7280]">Your case advances as documents, negotiations and lender confirmations are completed.</p></div>
                <div class="grid gap-0 p-5 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($caseStages as $key => $label)
                        @php($index = array_search($key, $stageKeys, true))
                        <div class="relative border-l-2 {{ $index <= $currentIndex ? 'border-[#42b883]' : 'border-[#dfe5ec]' }} pb-5 pl-4 last:pb-0">
                            <span class="absolute -left-[7px] top-0 h-3 w-3 rounded-full {{ $index <= $currentIndex ? 'bg-[#42b883]' : 'bg-[#dfe5ec]' }}"></span>
                            <p class="text-sm font-semibold {{ $key === $currentStage ? 'text-[#0d7a51]' : 'text-[#374151]' }}">{{ $label }}</p>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="overflow-hidden rounded-xl border border-[#dfe5ec] bg-white shadow-sm">
                <div class="border-b border-[#e7edf3] px-5 py-5"><h2 class="text-xl font-semibold">Lender settlement tracker</h2></div>
                <div class="space-y-3 p-4 md:hidden">
                    @forelse ($accounts as $account)
                        <article class="rounded-lg border border-[#e1e7ee] p-4">
                            <div class="flex items-start justify-between gap-3"><div class="min-w-0"><p class="truncate font-semibold">{{ $account->bank_name }}</p><p class="mt-1 text-xs text-[#6b7280]">{{ $account->product ?: 'Credit account' }}</p></div><span class="shrink-0 rounded-full bg-[#eef4fb] px-2.5 py-1 text-xs font-semibold text-[#315582]">{{ \App\Models\SettlementAccount::STAGES[$account->stage] ?? ucfirst($account->stage) }}</span></div>
                            <dl class="mt-4 grid grid-cols-2 gap-3 text-sm"><div><dt class="text-xs text-[#6b7280]">Outstanding</dt><dd class="mt-1 font-semibold">{{ $money($account->outstanding_amount) }}</dd></div><div><dt class="text-xs text-[#6b7280]">Offer / final</dt><dd class="mt-1 font-semibold">{{ $account->final_settlement_amount ? $money($account->final_settlement_amount) : ($account->offered_settlement_amount ? $money($account->offered_settlement_amount) : 'Pending') }}</dd></div><div class="col-span-2"><dt class="text-xs text-[#6b7280]">Due date</dt><dd class="mt-1 font-semibold">{{ $account->due_date?->format('d M Y') ?? '—' }}</dd></div></dl>
                        </article>
                    @empty
                        <p class="py-6 text-center text-sm text-[#6b7280]">Your case team is preparing the lender-wise settlement tracker.</p>
                    @endforelse
                </div>
                <div class="hidden overflow-x-auto md:block">
                    <table class="min-w-full text-left text-sm">
                        <thead class="bg-[#f7f9fb] text-xs uppercase tracking-wide text-[#6b7280]"><tr><th class="px-5 py-3">Lender</th><th class="px-5 py-3">Outstanding</th><th class="px-5 py-3">Offer/final</th><th class="px-5 py-3">Stage</th><th class="px-5 py-3">Due</th></tr></thead>
                        <tbody class="divide-y divide-[#edf1f5]">
                            @forelse ($accounts as $account)
                                <tr><td class="px-5 py-4"><p class="font-semibold">{{ $account->bank_name }}</p><p class="mt-1 text-xs text-[#6b7280]">{{ $account->product ?: 'Credit account' }} @if($account->account_reference) · {{ $account->account_reference }} @endif</p></td><td class="px-5 py-4 font-medium">{{ $money($account->outstanding_amount) }}</td><td class="px-5 py-4">{{ $account->final_settlement_amount ? $money($account->final_settlement_amount) : ($account->offered_settlement_amount ? $money($account->offered_settlement_amount) : 'Pending') }}</td><td class="px-5 py-4"><span class="rounded-full bg-[#eef4fb] px-3 py-1 text-xs font-semibold text-[#315582]">{{ \App\Models\SettlementAccount::STAGES[$account->stage] ?? ucfirst($account->stage) }}</span></td><td class="px-5 py-4">{{ $account->due_date?->format('d M Y') ?? '—' }}</td></tr>
                            @empty
                                <tr><td colspan="5" class="px-5 py-10 text-center text-[#6b7280]">Your case team is preparing the lender-wise settlement tracker.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="rounded-xl border border-[#dfe5ec] bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between"><div><h2 class="text-xl font-semibold">Document centre</h2><p class="mt-1 text-sm text-[#6b7280]">Files are stored privately and only accessible to you and your case team.</p></div></div>
                <form method="POST" action="{{ route('portal.documents.store') }}" enctype="multipart/form-data" class="mt-5 grid gap-3 rounded-xl bg-[#f7f9fb] p-4 md:grid-cols-[220px_1fr_auto]">
                    @csrf
                    <select name="document_type" required class="rounded-lg border border-[#cfd7e2] bg-white px-3 py-2.5 text-sm">@foreach($documentTypes as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach</select>
                    <input name="document" type="file" required accept=".pdf,.jpg,.jpeg,.png,.webp" class="min-w-0 w-full rounded-lg border border-[#cfd7e2] bg-white px-3 py-2 text-sm">
                    <button class="rounded-lg bg-[#10223f] px-5 py-2.5 text-sm font-semibold text-white">Upload securely</button>
                </form>
                @error('document')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                <div class="mt-5 grid gap-3 md:grid-cols-2">
                    @forelse($documents as $document)
                        <div class="rounded-lg border border-[#e1e7ee] p-4"><div class="flex items-start justify-between gap-3"><div><p class="font-semibold">{{ $documentTypes[$document->document_type] ?? 'Document' }}</p><p class="mt-1 break-all text-xs text-[#6b7280]">{{ $document->original_name }}</p></div><span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $document->review_status === 'accepted' ? 'bg-green-50 text-green-700' : ($document->review_status === 'rejected' ? 'bg-red-50 text-red-700' : 'bg-amber-50 text-amber-700') }}">{{ \App\Models\LeadDocument::REVIEW_STATUSES[$document->review_status] ?? 'Under review' }}</span></div>@if($document->review_notes)<p class="mt-3 text-sm text-[#5f6b7a]">{{ $document->review_notes }}</p>@endif<a href="{{ route('portal.documents.download', $document) }}" class="mt-3 inline-flex text-sm font-semibold text-[#10223f] hover:underline">Download</a></div>
                    @empty<p class="text-sm text-[#6b7280]">No documents uploaded yet.</p>@endforelse
                </div>
            </div>
        </div>

        <aside class="space-y-6">
            <div class="rounded-xl border border-[#e6d6a4] bg-[#fffaf0] p-5 shadow-sm">
                <h2 class="text-lg font-semibold">Action required</h2>
                <div class="mt-4 space-y-3">
                    @forelse($pendingTasks as $task)
                        <div class="rounded-lg border border-[#ead8a8] bg-white p-4">
                            <div class="flex justify-between gap-3"><p class="font-semibold">{{ $task->title }}</p><span class="text-xs font-semibold uppercase text-[#9a6b18]">{{ $task->priority }}</span></div>
                            @if($task->description)<p class="mt-2 text-sm leading-6 text-[#5f6b7a]">{{ $task->description }}</p>@endif
                            @if($task->due_at)<p class="mt-2 text-xs font-semibold text-[#b45309]">Due {{ $task->due_at->format('d M Y, h:i A') }}</p>@endif
                            <form method="POST" action="{{ route('portal.tasks.complete', $task) }}" class="mt-3">@csrf @method('PATCH')<button class="rounded-lg bg-[#10223f] px-3 py-2 text-xs font-semibold text-white">Mark complete</button></form>
                        </div>
                    @empty
                        <p class="rounded-lg bg-white p-4 text-sm text-[#5f6b7a]">No pending actions. Your case team will notify you when something is needed.</p>
                    @endforelse
                </div>
            </div>

            <div class="rounded-xl border border-[#dfe5ec] bg-white p-5 shadow-sm">
                <h2 class="text-lg font-semibold">Legal notices</h2>
                <div class="mt-4 space-y-3">
                    @forelse($legalNotices as $notice)
                        <div class="rounded-lg border {{ $notice->priority === 'urgent' ? 'border-red-200 bg-red-50' : 'border-[#e1e7ee]' }} p-4">
                            <p class="font-semibold">{{ $notice->lender_name }}</p>
                            <p class="mt-1 text-sm text-[#5f6b7a]">{{ $notice->notice_type }} · {{ $notice->received_at->format('d M Y') }}</p>
                            <p class="mt-2 text-xs font-semibold uppercase text-[#6b7280]">{{ \App\Models\LegalNotice::STATUSES[$notice->status] ?? $notice->status }}</p>
                            @if($notice->response_due_at)<p class="mt-2 text-sm font-semibold text-red-700">Response due {{ $notice->response_due_at->format('d M Y') }}</p>@endif
                            @if($notice->customer_instructions)<p class="mt-2 text-sm leading-6 text-[#4b5563]">{{ $notice->customer_instructions }}</p>@endif
                            @if($notice->path)<a href="{{ route('portal.notices.download', $notice) }}" class="mt-3 inline-flex text-sm font-semibold text-[#10223f] hover:underline">View notice</a>@endif
                        </div>
                    @empty
                        <p class="text-sm text-[#6b7280]">No legal notices have been recorded.</p>
                    @endforelse
                </div>
            </div>

            <div class="rounded-xl border border-[#dfe5ec] bg-white p-5 shadow-sm"><h2 class="text-lg font-semibold">Recent updates</h2><div class="mt-5 space-y-5">@forelse($activities as $activity)<div class="border-l-2 border-[#42b883] pl-4"><p class="text-sm font-semibold">{{ $activity->event }}</p>@if($activity->notes)<p class="mt-1 text-sm leading-6 text-[#5f6b7a]">{{ $activity->notes }}</p>@endif<p class="mt-1 text-xs text-[#9ca3af]">{{ $activity->created_at->format('d M Y, h:i A') }}</p></div>@empty<p class="text-sm text-[#6b7280]">Updates from your case team will appear here.</p>@endforelse</div></div>
        </aside>
    </div>
</section>
@endsection
