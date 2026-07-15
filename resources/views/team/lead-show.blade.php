@extends('layouts.team')

@section('title', $lead->name.' | Lead')
@section('page_title', $lead->name)
@section('page_subtitle', 'Lead profile, follow-ups, documents, activity, and settlement tracker.')
@section('search_action', route('team.home'))

@section('content')
    @php
        $canSales = auth()->user()->isAdmin() || (auth()->user()->isSales() && $lead->assigned_sales_id === auth()->id());
        $canRm = auth()->user()->isAdmin() || (auth()->user()->isRm() && $lead->assigned_rm_id === auth()->id());
        $income = $lead->income !== null ? 'Rs. '.number_format($lead->income) : '-';
        $formatRupees = fn (int $amount) => 'Rs. '.number_format($amount);
        $scorePercent = $creditScore !== null ? min(100, max(0, ($creditScore - 300) / 6)) : 0;
        $remainingPercent = min(100, round(($totalRemaining / max(1, $totalPrincipal)) * 100));
    @endphp

    <section class="space-y-5">
        <div class="crm-card p-5">
            <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                <div>
                    <a href="{{ url()->previous() }}" class="text-sm font-semibold text-[#10223f] hover:underline">Back</a>
                    <p class="mt-4 text-xs font-semibold uppercase tracking-wide text-[#b7862d]">Lead #{{ $lead->id }}</p>
                    <h1 class="mt-2 text-2xl font-semibold text-[#111827]">{{ $lead->name }}</h1>
                    <div class="mt-3 flex flex-wrap gap-x-4 gap-y-2 text-sm text-[#5f6b7a]">
                        <span>+91 {{ $lead->mobile }}</span>
                        <span>{{ $lead->email }}</span>
                        <span>PAN {{ $lead->pan_card }}</span>
                        <span>Age {{ $lead->age ?? '-' }}</span>
                        <span>Income {{ $income }}</span>
                    </div>
                </div>

                <div class="flex flex-wrap gap-2 md:justify-end">
                    <span class="rounded-full bg-[#eef4fb] px-3 py-1 text-xs font-semibold text-[#10223f]">{{ $salesStatuses[$lead->sales_status] ?? 'Sales pending' }}</span>
                    <span class="rounded-full bg-[#f7f9fb] px-3 py-1 text-xs font-semibold text-[#4b5563]">{{ $rmStatuses[$lead->rm_status] ?? 'RM pending' }}</span>
                    @if ($lead->consultation_fee_paid_at)
                        <span class="rounded-full bg-[#eef8f3] px-3 py-1 text-xs font-semibold text-[#0d7a51]">Rs. 99 paid</span>
                    @endif
                    @if ($lead->service_fee_paid_at)
                        <span class="rounded-full bg-[#fff7e7] px-3 py-1 text-xs font-semibold text-[#9a6b18]">Service activated</span>
                    @endif
                </div>
            </div>

            <div class="mt-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-6">
                <div class="rounded-lg border border-[#e1e7ee] p-3">
                    <p class="text-xs font-semibold uppercase tracking-wide text-[#5f6b7a]">Sales owner</p>
                    <p class="mt-2 text-sm font-semibold text-[#111827]">{{ $lead->assignedSales?->name ?? 'Unassigned' }}</p>
                </div>
                <div class="rounded-lg border border-[#e1e7ee] p-3">
                    <p class="text-xs font-semibold uppercase tracking-wide text-[#5f6b7a]">RM owner</p>
                    <p class="mt-2 text-sm font-semibold text-[#111827]">{{ $lead->assignedRm?->name ?? 'Unassigned' }}</p>
                </div>
                <div class="rounded-lg border border-[#e1e7ee] p-3">
                    <p class="text-xs font-semibold uppercase tracking-wide text-[#5f6b7a]">Priority</p>
                    <p class="mt-2 text-sm font-semibold text-[#111827]">{{ $priorities[$lead->priority] ?? 'Normal' }}</p>
                </div>
                <div class="rounded-lg border border-[#e1e7ee] p-3">
                    <p class="text-xs font-semibold uppercase tracking-wide text-[#5f6b7a]">Follow-up</p>
                    <p class="mt-2 text-sm font-semibold text-[#111827]">{{ optional($lead->follow_up_at)->format('M d, Y h:i A') ?? 'Not set' }}</p>
                </div>
                <div class="rounded-lg border border-[#e1e7ee] p-3">
                    <p class="text-xs font-semibold uppercase tracking-wide text-[#5f6b7a]">Call outcome</p>
                    <p class="mt-2 text-sm font-semibold text-[#111827]">{{ $callDispositions[$lead->call_disposition] ?? 'Not set' }}</p>
                </div>
                <div class="rounded-lg border border-[#e1e7ee] p-3">
                    <p class="text-xs font-semibold uppercase tracking-wide text-[#5f6b7a]">Income</p>
                    <p class="mt-2 text-sm font-semibold text-[#111827]">{{ $income }}</p>
                </div>
            </div>
        </div>

        <div class="grid gap-5 lg:grid-cols-[1.25fr_0.75fr]">
            <div class="space-y-5">
                <div class="crm-card overflow-hidden">
                    <div class="bg-[#10223f] px-5 py-6 text-white">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <span class="rounded-full bg-[#f4c877] px-3 py-1 text-xs font-semibold uppercase tracking-wide text-[#10223f]">CRIF credit report</span>
                                <h2 class="mt-4 text-2xl font-semibold">Verified bureau data</h2>
                                <p class="mt-2 text-sm text-white/70">Values below come from the customer's latest completed CRIF report.</p>
                            </div>
                            <span class="w-fit rounded-full bg-white/10 px-3 py-1 text-xs font-semibold text-white/80">
                                {{ $reportFetchedAt ? 'Fetched '.$reportFetchedAt->format('M d, Y h:i A') : 'Report unavailable' }}
                            </span>
                        </div>
                        <div class="mt-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                            <div class="rounded-lg border border-white/15 bg-white/10 p-4"><p class="text-xs uppercase text-white/60">CRIF score</p><p class="mt-2 text-2xl font-semibold">{{ $creditScore ?? '—' }}</p></div>
                            <div class="rounded-lg border border-white/15 bg-white/10 p-4"><p class="text-xs uppercase text-white/60">Accounts</p><p class="mt-2 text-2xl font-semibold">{{ count($accounts) }}</p></div>
                            <div class="rounded-lg border border-white/15 bg-white/10 p-4"><p class="text-xs uppercase text-white/60">Total outstanding</p><p class="mt-2 text-2xl font-semibold">{{ $formatRupees($totalRemaining) }}</p></div>
                            <div class="rounded-lg border border-white/15 bg-white/10 p-4"><p class="text-xs uppercase text-white/60">Highest DPD</p><p class="mt-2 text-2xl font-semibold">{{ $highestDpd }} days</p></div>
                        </div>
                    </div>

                    <div class="border-t border-[#edf1f6] px-5 py-5">
                        <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                            <div>
                                <h3 class="text-lg font-semibold text-[#111827]">Account details</h3>
                                <p class="mt-1 text-sm text-[#64728a]">Use verified balances and repayment status during the customer review.</p>
                            </div>
                            <span class="w-fit rounded-full bg-[#eef8f3] px-3 py-1 text-xs font-semibold text-[#0d7a51]">No estimated settlement values</span>
                        </div>

                        <div class="mt-4 space-y-3 md:hidden">
                            @forelse ($accounts as $account)
                                <article class="rounded-lg border border-[#e1e7ee] p-4">
                                    <div class="flex items-start justify-between gap-3"><div><p class="font-semibold">{{ $account['bank_name'] }}</p><p class="mt-1 text-xs text-[#6b7280]">{{ $account['product'] }}</p></div><span class="rounded-full bg-[#eef4fb] px-2 py-1 text-xs font-semibold capitalize">{{ $account['status'] }}</span></div>
                                    <dl class="mt-4 grid grid-cols-2 gap-3 text-sm"><div><dt class="text-xs text-[#6b7280]">Outstanding</dt><dd class="mt-1 font-semibold">{{ $formatRupees($account['remaining_amount']) }}</dd></div><div><dt class="text-xs text-[#6b7280]">DPD</dt><dd class="mt-1 font-semibold">{{ $account['dpd'] }} days</dd></div></dl>
                                </article>
                            @empty
                                <p class="rounded-lg bg-[#f7f9fb] p-4 text-sm text-[#64728a]">No recognizable accounts were returned in the completed CRIF report.</p>
                            @endforelse
                        </div>

                        <div class="mt-4 hidden overflow-x-auto md:block">
                            <table class="min-w-full text-left text-sm">
                                <thead class="bg-[#f7f9fb] text-xs font-semibold uppercase tracking-wide text-[#5f6b7a]">
                                    <tr>
                                        <th class="px-4 py-3">Account</th>
                                        <th class="px-4 py-3">Outstanding</th>
                                        <th class="px-4 py-3">DPD</th>
                                        <th class="px-4 py-3">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-[#edf1f6]">
                                    @forelse ($accounts as $account)
                                        @php
                                            $dpdClass = match (true) {
                                                $account['dpd'] === 0 => 'bg-[#eef8f3] text-[#0d7a51]',
                                                $account['dpd'] <= 30 => 'bg-[#fff7e7] text-[#9a6b18]',
                                                default => 'bg-[#fff1f0] text-[#b42318]',
                                            };
                                        @endphp
                                        <tr class="align-top">
                                            <td class="px-4 py-4">
                                                <p class="font-semibold text-[#111827]">{{ $account['bank_name'] }}</p>
                                                <p class="mt-1 text-xs text-[#6b7280]">{{ $account['product'] }}</p>
                                            </td>
                                            <td class="px-4 py-4">
                                                <p class="whitespace-nowrap font-semibold text-[#111827]">{{ $formatRupees($account['remaining_amount']) }}</p>
                                                <p class="mt-1 text-xs text-[#6b7280]">Principal {{ $formatRupees($account['principal_amount']) }}</p>
                                            </td>
                                            <td class="px-4 py-4">
                                                <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $dpdClass }}">{{ $account['dpd'] }} days</span>
                                            </td>
                                            <td class="px-4 py-4">
                                                <span class="inline-flex rounded-full bg-[#eef4fb] px-3 py-1 text-xs font-semibold capitalize text-[#315582]">{{ $account['status'] }}</span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="4" class="px-4 py-8 text-center text-[#64728a]">No recognizable accounts were returned in the completed CRIF report.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                @if (auth()->user()->isAdmin())
                    <div class="crm-card p-5">
                        <h2 class="text-lg font-semibold text-[#111827]">Assignment</h2>
                        <form method="POST" action="{{ route('team.leads.assignment', $lead) }}" class="mt-4 grid gap-4 md:grid-cols-[1fr_1fr_auto] md:items-end">
                            @csrf
                            @method('PATCH')
                            <div>
                                <label for="assigned_sales_id" class="block text-sm font-medium text-[#374151]">Sales owner</label>
                                <select id="assigned_sales_id" name="assigned_sales_id" class="mt-2 w-full rounded-md border border-[#cfd7e2] px-3 py-2 text-sm outline-none focus:border-[#10223f] focus:ring-2 focus:ring-[#10223f]/10">
                                    <option value="">Unassigned</option>
                                    @foreach ($salesUsers as $sales)
                                        <option value="{{ $sales->id }}" @selected($lead->assigned_sales_id === $sales->id)>{{ $sales->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="assigned_rm_id" class="block text-sm font-medium text-[#374151]">RM owner</label>
                                <select id="assigned_rm_id" name="assigned_rm_id" class="mt-2 w-full rounded-md border border-[#cfd7e2] px-3 py-2 text-sm outline-none focus:border-[#10223f] focus:ring-2 focus:ring-[#10223f]/10">
                                    <option value="">Unassigned</option>
                                    @foreach ($rmUsers as $rm)
                                        <option value="{{ $rm->id }}" @selected($lead->assigned_rm_id === $rm->id)>{{ $rm->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <button type="submit" class="rounded-md bg-[#10223f] px-4 py-2 text-sm font-semibold text-white hover:bg-[#18365f]">Save assignment</button>
                        </form>
                    </div>
                @endif

                @if ($canSales)
                    <div class="crm-card p-5">
                        <h2 class="text-lg font-semibold text-[#111827]">Sales update</h2>
                        <form method="POST" action="{{ route('team.leads.sales', $lead) }}" class="mt-4 space-y-4">
                            @csrf
                            @method('PATCH')
                            <div class="grid gap-4 md:grid-cols-3">
                                <div>
                                    <label for="sales_status" class="block text-sm font-medium text-[#374151]">Sales status</label>
                                    <select id="sales_status" name="sales_status" class="mt-2 w-full rounded-md border border-[#cfd7e2] px-3 py-2 text-sm outline-none focus:border-[#10223f] focus:ring-2 focus:ring-[#10223f]/10">
                                        @foreach ($salesStatuses as $value => $label)
                                            <option value="{{ $value }}" @selected($lead->sales_status === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label for="call_disposition" class="block text-sm font-medium text-[#374151]">Call disposition</label>
                                    <select id="call_disposition" name="call_disposition" class="mt-2 w-full rounded-md border border-[#cfd7e2] px-3 py-2 text-sm outline-none focus:border-[#10223f] focus:ring-2 focus:ring-[#10223f]/10">
                                        <option value="">Select</option>
                                        @foreach ($callDispositions as $value => $label)
                                            <option value="{{ $value }}" @selected($lead->call_disposition === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label for="priority" class="block text-sm font-medium text-[#374151]">Priority</label>
                                    <select id="priority" name="priority" class="mt-2 w-full rounded-md border border-[#cfd7e2] px-3 py-2 text-sm outline-none focus:border-[#10223f] focus:ring-2 focus:ring-[#10223f]/10">
                                        @foreach ($priorities as $value => $label)
                                            <option value="{{ $value }}" @selected(($lead->priority ?? 'normal') === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="grid gap-4 md:grid-cols-[1fr_2fr]">
                                <div>
                                    <label for="follow_up_at" class="block text-sm font-medium text-[#374151]">Follow-up</label>
                                    <input id="follow_up_at" name="follow_up_at" type="datetime-local" value="{{ optional($lead->follow_up_at)->format('Y-m-d\TH:i') }}" class="mt-2 w-full rounded-md border border-[#cfd7e2] px-3 py-2 text-sm outline-none focus:border-[#10223f] focus:ring-2 focus:ring-[#10223f]/10">
                                </div>
                                <div>
                                    <label for="sales_notes" class="block text-sm font-medium text-[#374151]">Sales notes</label>
                                    <textarea id="sales_notes" name="sales_notes" rows="3" class="mt-2 w-full rounded-md border border-[#cfd7e2] px-3 py-2 text-sm outline-none focus:border-[#10223f] focus:ring-2 focus:ring-[#10223f]/10">{{ old('sales_notes', $lead->sales_notes) }}</textarea>
                                </div>
                            </div>
                            <label class="flex items-start gap-3 rounded-md bg-[#fff8e8] p-3 text-sm text-[#374151]">
                                <input type="checkbox" name="mark_service_fee_paid" value="1" class="mt-1 rounded border-[#cfd7e2]" @checked($lead->service_fee_paid_at)>
                                <span>Mark settlement service activated and send this lead to RM dashboard.</span>
                            </label>
                            <button type="submit" class="rounded-md bg-[#10223f] px-4 py-3 text-sm font-semibold text-white hover:bg-[#18365f]">Save sales update</button>
                        </form>
                    </div>
                @endif

                @if ($canRm && $lead->service_fee_paid_at)
                    <div class="crm-card p-5">
                        <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                            <div><p class="text-xs font-semibold uppercase tracking-wide text-[#b7862d]">Customer portal</p><h2 class="mt-2 text-lg font-semibold text-[#111827]">Case stage</h2><p class="mt-1 text-sm text-[#6b7280]">Reference {{ $lead->case_reference ?: 'created on first portal visit' }}</p></div>
                            <form method="POST" action="{{ route('team.leads.case', $lead) }}" class="flex min-w-0 flex-1 gap-3 md:max-w-xl">
                                @csrf @method('PATCH')
                                <select name="case_stage" class="min-w-0 flex-1 rounded-md border border-[#cfd7e2] px-3 py-2 text-sm">@foreach($caseStages as $value => $label)<option value="{{ $value }}" @selected(($lead->case_stage ?: 'enrolled') === $value)>{{ $label }}</option>@endforeach</select>
                                <button class="rounded-md bg-[#10223f] px-4 py-2 text-sm font-semibold text-white">Update stage</button>
                            </form>
                        </div>
                    </div>

                    <div class="crm-card p-5">
                        <h2 class="text-lg font-semibold text-[#111827]">RM update</h2>
                        <form method="POST" action="{{ route('team.leads.rm', $lead) }}" class="mt-4 space-y-4">
                            @csrf
                            @method('PATCH')
                            <div>
                                <label for="rm_status" class="block text-sm font-medium text-[#374151]">RM status</label>
                                <select id="rm_status" name="rm_status" class="mt-2 w-full rounded-md border border-[#cfd7e2] px-3 py-2 text-sm outline-none focus:border-[#10223f] focus:ring-2 focus:ring-[#10223f]/10">
                                    @foreach ($rmStatuses as $value => $label)
                                        <option value="{{ $value }}" @selected($lead->rm_status === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="rm_notes" class="block text-sm font-medium text-[#374151]">RM notes</label>
                                <textarea id="rm_notes" name="rm_notes" rows="4" class="mt-2 w-full rounded-md border border-[#cfd7e2] px-3 py-2 text-sm outline-none focus:border-[#10223f] focus:ring-2 focus:ring-[#10223f]/10">{{ old('rm_notes', $lead->rm_notes) }}</textarea>
                            </div>
                            <button type="submit" class="rounded-md bg-[#10223f] px-4 py-3 text-sm font-semibold text-white hover:bg-[#18365f]">Save RM update</button>
                        </form>
                    </div>
                @endif

                @if ($canRm && $lead->service_fee_paid_at)
                    <div class="crm-card p-5">
                        <h2 class="text-lg font-semibold text-[#111827]">Settlement tracker</h2>
                        <div class="mt-4 space-y-4">
                            @foreach ($lead->settlementAccounts as $account)
                                <form method="POST" action="{{ route('team.settlement-accounts.update', $account) }}" class="rounded-lg border border-[#e1e7ee] p-4">
                                    @csrf
                                    @method('PATCH')
                                    <div class="grid gap-3 md:grid-cols-3">
                                        <input name="bank_name" value="{{ $account->bank_name }}" required class="rounded-md border border-[#cfd7e2] px-3 py-2 text-sm outline-none focus:border-[#10223f] focus:ring-2 focus:ring-[#10223f]/10">
                                        <input name="product" value="{{ $account->product }}" placeholder="Loan/product" class="rounded-md border border-[#cfd7e2] px-3 py-2 text-sm">
                                        <input name="account_reference" value="{{ $account->account_reference }}" placeholder="Masked account reference" class="rounded-md border border-[#cfd7e2] px-3 py-2 text-sm">
                                        <select name="stage" class="rounded-md border border-[#cfd7e2] px-3 py-2 text-sm">@foreach($settlementStages as $value => $label)<option value="{{ $value }}" @selected($account->stage === $value)>{{ $label }}</option>@endforeach</select>
                                        <input name="outstanding_amount" value="{{ $account->outstanding_amount }}" required inputmode="numeric" class="rounded-md border border-[#cfd7e2] px-3 py-2 text-sm outline-none focus:border-[#10223f] focus:ring-2 focus:ring-[#10223f]/10">
                                        <input name="offered_settlement_amount" value="{{ $account->offered_settlement_amount }}" inputmode="numeric" placeholder="Offered amount" class="rounded-md border border-[#cfd7e2] px-3 py-2 text-sm outline-none focus:border-[#10223f] focus:ring-2 focus:ring-[#10223f]/10">
                                        <input name="final_settlement_amount" value="{{ $account->final_settlement_amount }}" inputmode="numeric" placeholder="Final amount" class="rounded-md border border-[#cfd7e2] px-3 py-2 text-sm outline-none focus:border-[#10223f] focus:ring-2 focus:ring-[#10223f]/10">
                                        <input name="due_date" type="date" value="{{ optional($account->due_date)->format('Y-m-d') }}" class="rounded-md border border-[#cfd7e2] px-3 py-2 text-sm outline-none focus:border-[#10223f] focus:ring-2 focus:ring-[#10223f]/10">
                                        <select name="closure_letter_status" class="rounded-md border border-[#cfd7e2] px-3 py-2 text-sm outline-none focus:border-[#10223f] focus:ring-2 focus:ring-[#10223f]/10">
                                            @foreach ($closureStatuses as $value => $label)
                                                <option value="{{ $value }}" @selected($account->closure_letter_status === $value)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <label class="mt-3 flex items-center gap-2 text-sm text-[#4b5563]"><input type="checkbox" name="customer_visible" value="1" @checked($account->customer_visible)> Show this account in customer portal</label>
                                    <textarea name="notes" rows="2" placeholder="Settlement notes" class="mt-3 w-full rounded-md border border-[#cfd7e2] px-3 py-2 text-sm outline-none focus:border-[#10223f] focus:ring-2 focus:ring-[#10223f]/10">{{ $account->notes }}</textarea>
                                    <button type="submit" class="mt-3 rounded-md bg-[#f4c877] px-4 py-2 text-sm font-semibold text-[#10223f] hover:bg-[#e8bb62]">Update tracker</button>
                                </form>
                            @endforeach
                        </div>

                        <form method="POST" action="{{ route('team.leads.settlement-accounts', $lead) }}" class="mt-5 rounded-lg border border-[#e1e7ee] p-4">
                            @csrf
                            <h3 class="text-sm font-semibold uppercase tracking-wide text-[#5f6b7a]">Add bank account</h3>
                            <div class="mt-3 grid gap-3 md:grid-cols-3">
                                <input name="bank_name" required placeholder="Bank name" class="rounded-md border border-[#cfd7e2] px-3 py-2 text-sm outline-none focus:border-[#10223f] focus:ring-2 focus:ring-[#10223f]/10">
                                <input name="product" placeholder="Loan/product" class="rounded-md border border-[#cfd7e2] px-3 py-2 text-sm">
                                <input name="account_reference" placeholder="Masked account reference" class="rounded-md border border-[#cfd7e2] px-3 py-2 text-sm">
                                <select name="stage" class="rounded-md border border-[#cfd7e2] px-3 py-2 text-sm">@foreach($settlementStages as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach</select>
                                <input name="outstanding_amount" required inputmode="numeric" placeholder="Outstanding amount" class="rounded-md border border-[#cfd7e2] px-3 py-2 text-sm outline-none focus:border-[#10223f] focus:ring-2 focus:ring-[#10223f]/10">
                                <input name="offered_settlement_amount" inputmode="numeric" placeholder="Offered amount" class="rounded-md border border-[#cfd7e2] px-3 py-2 text-sm outline-none focus:border-[#10223f] focus:ring-2 focus:ring-[#10223f]/10">
                                <input name="final_settlement_amount" inputmode="numeric" placeholder="Final amount" class="rounded-md border border-[#cfd7e2] px-3 py-2 text-sm outline-none focus:border-[#10223f] focus:ring-2 focus:ring-[#10223f]/10">
                                <input name="due_date" type="date" class="rounded-md border border-[#cfd7e2] px-3 py-2 text-sm outline-none focus:border-[#10223f] focus:ring-2 focus:ring-[#10223f]/10">
                                <select name="closure_letter_status" class="rounded-md border border-[#cfd7e2] px-3 py-2 text-sm outline-none focus:border-[#10223f] focus:ring-2 focus:ring-[#10223f]/10">
                                    @foreach ($closureStatuses as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <label class="mt-3 flex items-center gap-2 text-sm text-[#4b5563]"><input type="checkbox" name="customer_visible" value="1" checked> Show this account in customer portal</label>
                            <textarea name="notes" rows="2" placeholder="Settlement notes" class="mt-3 w-full rounded-md border border-[#cfd7e2] px-3 py-2 text-sm outline-none focus:border-[#10223f] focus:ring-2 focus:ring-[#10223f]/10"></textarea>
                            <button type="submit" class="mt-3 rounded-md bg-[#10223f] px-4 py-2 text-sm font-semibold text-white hover:bg-[#18365f]">Add tracker</button>
                        </form>
                    </div>
                @endif
            </div>

            <aside class="space-y-5">
                @if ($canRm && $lead->service_fee_paid_at)
                    <div class="crm-card p-5">
                        <h2 class="text-lg font-semibold text-[#111827]">Customer actions</h2>
                        <form method="POST" action="{{ route('team.leads.tasks', $lead) }}" class="mt-4 space-y-3">@csrf
                            <input name="title" required placeholder="Action title" class="w-full rounded-md border border-[#cfd7e2] px-3 py-2 text-sm">
                            <textarea name="description" rows="2" placeholder="Instructions for customer" class="w-full rounded-md border border-[#cfd7e2] px-3 py-2 text-sm"></textarea>
                            <div class="grid grid-cols-2 gap-3"><select name="priority" class="rounded-md border border-[#cfd7e2] px-3 py-2 text-sm">@foreach($taskPriorities as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach</select><input name="due_at" type="datetime-local" class="rounded-md border border-[#cfd7e2] px-3 py-2 text-sm"></div>
                            <button class="w-full rounded-md bg-[#10223f] px-4 py-2 text-sm font-semibold text-white">Create customer action</button>
                        </form>
                        <div class="mt-4 space-y-2">@foreach($lead->customerTasks->take(5) as $task)<div class="rounded-md border border-[#e1e7ee] p-3"><p class="text-sm font-semibold">{{ $task->title }}</p><p class="mt-1 text-xs uppercase text-[#6b7280]">{{ $task->status }} · {{ $task->priority }}</p></div>@endforeach</div>
                    </div>

                    <div class="crm-card p-5">
                        <h2 class="text-lg font-semibold text-[#111827]">Legal notice</h2>
                        <form method="POST" action="{{ route('team.leads.legal-notices', $lead) }}" enctype="multipart/form-data" class="mt-4 space-y-3">@csrf
                            <input name="lender_name" required placeholder="Lender / authority" class="w-full rounded-md border border-[#cfd7e2] px-3 py-2 text-sm">
                            <input name="notice_type" required placeholder="Notice type" class="w-full rounded-md border border-[#cfd7e2] px-3 py-2 text-sm">
                            <div class="grid grid-cols-2 gap-3"><input name="received_at" type="date" required class="rounded-md border border-[#cfd7e2] px-3 py-2 text-sm"><input name="response_due_at" type="date" class="rounded-md border border-[#cfd7e2] px-3 py-2 text-sm"></div>
                            <div class="grid grid-cols-2 gap-3"><select name="priority" class="rounded-md border border-[#cfd7e2] px-3 py-2 text-sm">@foreach($noticePriorities as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach</select><select name="status" class="rounded-md border border-[#cfd7e2] px-3 py-2 text-sm">@foreach($noticeStatuses as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach</select></div>
                            <textarea name="customer_instructions" rows="3" placeholder="Instructions visible to customer" class="w-full rounded-md border border-[#cfd7e2] px-3 py-2 text-sm"></textarea>
                            <input name="notice" type="file" accept=".pdf,.jpg,.jpeg,.png,.webp" class="w-full rounded-md border border-[#cfd7e2] px-3 py-2 text-sm">
                            <button class="w-full rounded-md bg-[#10223f] px-4 py-2 text-sm font-semibold text-white">Add legal notice</button>
                        </form>
                    </div>
                @endif

                <div class="crm-card p-5">
                    <h2 class="text-lg font-semibold text-[#111827]">WhatsApp templates</h2>
                    <div class="mt-4 space-y-3">
                        @foreach ($whatsappTemplates as $template)
                            <a
                                href="https://wa.me/91{{ $lead->mobile }}?text={{ rawurlencode($template['message']) }}"
                                target="_blank"
                                class="block rounded-md border border-[#d6efe4] bg-[#f2fbf7] px-4 py-3 text-sm font-semibold text-[#0d7a51] hover:bg-[#e6f7ef]"
                            >
                                {{ $template['label'] }}
                            </a>
                        @endforeach
                    </div>
                </div>

                <div class="crm-card p-5">
                    <h2 class="text-lg font-semibold text-[#111827]">Documents</h2>
                    <form method="POST" action="{{ route('team.leads.documents', $lead) }}" enctype="multipart/form-data" class="mt-4 space-y-3">
                        @csrf
                        <select name="document_type" class="w-full rounded-md border border-[#cfd7e2] px-3 py-2 text-sm outline-none focus:border-[#10223f] focus:ring-2 focus:ring-[#10223f]/10">
                            @foreach ($documentTypes as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        <input name="document" type="file" required class="w-full rounded-md border border-[#cfd7e2] px-3 py-2 text-sm">
                        <button type="submit" class="w-full rounded-md bg-[#10223f] px-4 py-2 text-sm font-semibold text-white hover:bg-[#18365f]">Upload document</button>
                    </form>

                    <div class="mt-5 space-y-3">
                        @forelse ($lead->leadDocuments as $document)
                            <div class="rounded-md border border-[#e1e7ee] p-3">
                                <p class="text-sm font-semibold text-[#111827]">{{ $documentTypes[$document->document_type] ?? 'Document' }}</p>
                                <p class="mt-1 break-words text-xs text-[#6b7280]">{{ $document->original_name }}</p>
                                <a href="{{ route('team.documents.download', $document) }}" class="mt-2 inline-flex text-sm font-semibold text-[#10223f] hover:underline">Download</a>
                                @if($canRm && $lead->service_fee_paid_at)
                                    <form method="POST" action="{{ route('team.documents.review', $document) }}" class="mt-3 space-y-2">@csrf @method('PATCH')
                                        <select name="review_status" class="w-full rounded-md border border-[#cfd7e2] px-2 py-1.5 text-xs">@foreach(\App\Models\LeadDocument::REVIEW_STATUSES as $value => $label)<option value="{{ $value }}" @selected($document->review_status === $value)>{{ $label }}</option>@endforeach</select>
                                        <input name="review_notes" value="{{ $document->review_notes }}" placeholder="Customer-visible review note" class="w-full rounded-md border border-[#cfd7e2] px-2 py-1.5 text-xs">
                                        <button class="text-xs font-semibold text-[#0d7a51]">Save review</button>
                                    </form>
                                @endif
                            </div>
                        @empty
                            <p class="text-sm text-[#6b7280]">No documents uploaded yet.</p>
                        @endforelse
                    </div>
                </div>

                <div class="crm-card p-5">
                    <h2 class="text-lg font-semibold text-[#111827]">Activity timeline</h2>
                    <form method="POST" action="{{ route('team.leads.activities', $lead) }}" class="mt-4 space-y-3">
                        @csrf
                        <textarea name="notes" rows="3" required placeholder="Add case update or internal note" class="w-full rounded-md border border-[#cfd7e2] px-3 py-2 text-sm outline-none focus:border-[#10223f] focus:ring-2 focus:ring-[#10223f]/10"></textarea>
                        <label class="flex items-center gap-2 text-xs text-[#4b5563]"><input type="checkbox" name="customer_visible" value="1"> Show this update to customer</label>
                        <button type="submit" class="w-full rounded-md bg-[#f4c877] px-4 py-2 text-sm font-semibold text-[#10223f] hover:bg-[#e8bb62]">Add note</button>
                    </form>

                    <div class="mt-5 space-y-4">
                        @forelse ($lead->leadActivities as $activity)
                            <div class="border-l-2 border-[#dfe5ec] pl-4">
                                <p class="text-sm font-semibold text-[#111827]">{{ $activity->event }}</p>
                                <p class="mt-1 text-xs text-[#6b7280]">
                                    {{ $activity->created_at->format('M d, Y h:i A') }}
                                    @if ($activity->actor)
                                        by {{ $activity->actor->name }}
                                    @endif
                                </p>
                                @if ($activity->notes)
                                    <p class="mt-2 text-sm leading-6 text-[#4b5563]">{{ $activity->notes }}</p>
                                @endif
                            </div>
                        @empty
                            <p class="text-sm text-[#6b7280]">No activity yet.</p>
                        @endforelse
                    </div>
                </div>
            </aside>
        </div>
    </section>
@endsection
