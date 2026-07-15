@extends('layouts.team')

@section('title', 'Admin Dashboard | Settle Pe CRM')
@section('page_title', 'Admin CRM')
@section('page_subtitle', 'Lead intake, sales conversion, RM cases, follow-ups, and settlement pipeline.')
@section('search_action', route('team.admin'))

@section('content')
    @php
        $formatRupees = fn (int $amount) => 'Rs. '.number_format($amount);
        $metricCards = [
            ['label' => 'Total Leads', 'value' => $metrics['customerLeads'], 'note' => $metrics['newToday'].' added today', 'tone' => 'bg-[#eef2ff] text-[#3858ff]'],
            ['label' => 'Unassigned Sales', 'value' => $metrics['unassignedSales'], 'note' => 'Needs owner', 'tone' => 'bg-[#fff7ed] text-[#c2410c]'],
            ['label' => 'Consultation Paid', 'value' => $metrics['paid99'], 'note' => 'Ready for calling', 'tone' => 'bg-[#eafaf2] text-[#0ca651]'],
            ['label' => 'Active RM Cases', 'value' => $metrics['activeCases'], 'note' => $metrics['unassignedRm'].' RM unassigned', 'tone' => 'bg-[#f3e8ff] text-[#7e22ce]'],
            ['label' => 'Due Follow-ups', 'value' => $metrics['todayFollowUps'], 'note' => 'Due now or overdue', 'tone' => 'bg-[#fff1f0] text-[#b42318]'],
            ['label' => 'Settlement Pipeline', 'value' => $formatRupees($financials['settlementPipeline']), 'note' => 'Tracked offers', 'tone' => 'bg-[#eaf3ff] text-[#1d4ed8]'],
        ];
        $stageTotal = max(1, collect($stageCounts)->sum('count'));
        $statusClass = fn (?string $status) => match ($status) {
            'consultation_paid' => 'bg-[#e9f2ff] text-[#1d4ed8]',
            'service_fee_paid' => 'bg-[#fff3dc] text-[#b45309]',
            'settlement_pitched' => 'bg-[#f3e8ff] text-[#7e22ce]',
            'follow_up' => 'bg-[#fff7ed] text-[#c2410c]',
            'not_interested' => 'bg-[#fee2e2] text-[#b91c1c]',
            default => 'bg-[#eef8f3] text-[#0d7a51]',
        };
    @endphp

    <section class="space-y-5">
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-6">
            @foreach ($metricCards as $metric)
                <div class="crm-card p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-xs font-black uppercase tracking-wide text-[#64728a]">{{ $metric['label'] }}</p>
                            <p class="mt-3 break-words text-2xl font-black text-[#07142b]">{{ $metric['value'] }}</p>
                        </div>
                        <span class="grid h-9 w-9 shrink-0 place-items-center rounded-lg text-xs font-black {{ $metric['tone'] }}">{{ substr($metric['label'], 0, 1) }}</span>
                    </div>
                    <p class="mt-3 text-sm font-semibold text-[#64728a]">{{ $metric['note'] }}</p>
                </div>
            @endforeach
        </div>

        <div class="grid gap-5 xl:grid-cols-[1fr_360px]">
            <div class="crm-card overflow-hidden">
                <div class="flex flex-col gap-4 border-b border-[#edf1f6] px-5 py-4 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <h2 class="text-lg font-black text-[#07142b]">Lead Operations</h2>
                        <p class="mt-1 text-sm text-[#64728a]">Search, filter, open a case, and assign Sales or RM from the detail page.</p>
                    </div>
                    <form method="GET" action="{{ route('team.admin') }}" class="grid gap-2 sm:grid-cols-[1fr_210px_auto]">
                        <input name="search" value="{{ $search }}" placeholder="Name, mobile, email, PAN" class="rounded-lg border border-[#dfe6f0] px-3 py-2 text-sm outline-none focus:border-[#4b63ff] focus:ring-2 focus:ring-[#4b63ff]/10">
                        <select name="status" class="rounded-lg border border-[#dfe6f0] px-3 py-2 text-sm outline-none focus:border-[#4b63ff] focus:ring-2 focus:ring-[#4b63ff]/10">
                            <option value="">All sales stages</option>
                            @foreach ($salesStatuses as $value => $label)
                                <option value="{{ $value }}" @selected($selectedStatus === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="rounded-lg bg-[#3858ff] px-4 py-2 text-sm font-bold text-white hover:bg-[#2442df]">Apply</button>
                    </form>
                </div>

                <div class="overflow-x-auto px-4 py-4">
                    <table class="crm-table min-w-full text-left text-sm">
                        <thead>
                            <tr>
                                <th>Case</th>
                                <th>Customer</th>
                                <th>Contact</th>
                                <th>Income</th>
                                <th>Stage</th>
                                <th>Owners</th>
                                <th>Next Follow-up</th>
                                <th>Service</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($leads as $lead)
                                <tr>
                                    <td class="font-black text-[#07142b]">CASE-{{ str_pad((string) $lead->id, 4, '0', STR_PAD_LEFT) }}</td>
                                    <td>
                                        <p class="font-black text-[#07142b]">{{ $lead->name }}</p>
                                        <p class="mt-1 text-xs text-[#64728a]">PAN {{ $lead->pan_card ?: '-' }}</p>
                                    </td>
                                    <td>
                                        <p>{{ $lead->mobile }}</p>
                                        <p class="mt-1 text-xs text-[#64728a]">{{ $lead->email }}</p>
                                    </td>
                                    <td>{{ $lead->income !== null ? 'Rs. '.number_format($lead->income) : '-' }}</td>
                                    <td><span class="status-pill {{ $statusClass($lead->sales_status) }}">{{ $salesStatuses[$lead->sales_status] ?? 'Lead Created' }}</span></td>
                                    <td>
                                        <p class="font-semibold text-[#07142b]">Sales: {{ $lead->assignedSales?->name ?? 'Unassigned' }}</p>
                                        <p class="mt-1 text-xs text-[#64728a]">RM: {{ $lead->assignedRm?->name ?? 'Unassigned' }}</p>
                                    </td>
                                    <td>{{ optional($lead->follow_up_at)->format('M d, h:i A') ?? '-' }}</td>
                                    <td><span class="status-pill {{ $lead->service_fee_paid_at ? 'bg-[#dcfce7] text-[#15803d]' : 'bg-[#fff7ed] text-[#c2410c]' }}">{{ $lead->service_fee_paid_at ? 'Active' : 'Pending' }}</span></td>
                                    <td class="text-right"><a href="{{ route('team.leads.show', $lead) }}" class="rounded-lg bg-[#07142b] px-3 py-2 text-xs font-bold text-white">Open</a></td>
                                </tr>
                            @empty
                                <tr><td colspan="9" class="text-center text-[#64728a]">No cases match this view.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($leads->hasPages())
                    <div class="border-t border-[#edf1f6] px-5 py-4">{{ $leads->links() }}</div>
                @endif
            </div>

            <div class="space-y-5">
                <div class="crm-card overflow-hidden">
                    <div class="flex items-center justify-between border-b border-[#edf1f6] px-5 py-4">
                        <h2 class="text-lg font-black text-[#07142b]">Due Follow-ups</h2>
                        <a href="{{ route('team.sales', ['status' => 'follow_up']) }}" class="text-sm font-bold text-[#3858ff]">Sales view</a>
                    </div>
                    <div class="divide-y divide-[#edf1f6]">
                        @forelse ($followUps as $followUp)
                            <a href="{{ route('team.leads.show', $followUp) }}" class="block px-5 py-4 transition hover:bg-[#f8fbff]">
                                <div class="flex items-start justify-between gap-4">
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-black text-[#07142b]">{{ $followUp->name }}</p>
                                        <p class="mt-1 truncate text-sm text-[#64728a]">{{ $followUp->sales_notes ?: 'Follow up with customer' }}</p>
                                    </div>
                                    <span class="shrink-0 rounded-lg bg-[#eef2ff] px-3 py-1 text-xs font-bold text-[#3858ff]">{{ optional($followUp->follow_up_at)->format('M d') }}</span>
                                </div>
                                <p class="mt-2 text-xs font-semibold text-[#64728a]">{{ optional($followUp->follow_up_at)->format('h:i A') }} by {{ $followUp->assignedSales?->name ?? 'Unassigned' }}</p>
                            </a>
                        @empty
                            <p class="px-5 py-8 text-center text-sm text-[#64728a]">No due follow-ups.</p>
                        @endforelse
                    </div>
                </div>

                <div class="crm-card p-5">
                    <h2 class="text-lg font-black text-[#07142b]">Financial Snapshot</h2>
                    <div class="mt-4 grid gap-3">
                        <div class="rounded-lg border border-[#dfe6f0] p-3">
                            <p class="text-xs font-bold uppercase text-[#64728a]">Consultation Fees</p>
                            <p class="mt-1 text-lg font-black text-[#07142b]">{{ $formatRupees($financials['consultationFees']) }}</p>
                        </div>
                        <div class="rounded-lg border border-[#dfe6f0] p-3">
                            <p class="text-xs font-bold uppercase text-[#64728a]">Settlement Pipeline</p>
                            <p class="mt-1 text-lg font-black text-[#07142b]">{{ $formatRupees($financials['settlementPipeline']) }}</p>
                        </div>
                        <div class="rounded-lg border border-[#dfe6f0] p-3">
                            <p class="text-xs font-bold uppercase text-[#64728a]">Outstanding Under Review</p>
                            <p class="mt-1 text-lg font-black text-[#07142b]">{{ $formatRupees($financials['outstandingUnderReview']) }}</p>
                        </div>
                        <div class="rounded-lg border border-[#dfe6f0] p-3">
                            <p class="text-xs font-bold uppercase text-[#64728a]">Documents Uploaded</p>
                            <p class="mt-1 text-lg font-black text-[#07142b]">{{ $financials['documentsUploaded'] }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="reports" class="grid gap-5 xl:grid-cols-[0.8fr_1fr_1fr]">
            <div class="crm-card p-5">
                <h2 class="text-lg font-black text-[#07142b]">Stage Mix</h2>
                <div class="mt-5 space-y-4">
                    @foreach ($stageCounts as $stage)
                        <div>
                            <div class="flex items-center justify-between gap-3 text-sm">
                                <div class="flex items-center gap-3">
                                    <span class="h-2.5 w-2.5 rounded-full" style="background: {{ $stage['color'] }}"></span>
                                    <span class="font-semibold text-[#526071]">{{ $stage['label'] }}</span>
                                </div>
                                <span class="font-black text-[#07142b]">{{ $stage['count'] }}</span>
                            </div>
                            <div class="mt-2 h-2 rounded-full bg-[#edf1f6]">
                                <div class="h-2 rounded-full" style="width: {{ round(($stage['count'] / $stageTotal) * 100, 1) }}%; background: {{ $stage['color'] }}"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="crm-card overflow-hidden">
                <div class="border-b border-[#edf1f6] px-5 py-4">
                    <h2 class="text-lg font-black text-[#07142b]">Sales Performance</h2>
                </div>
                <div class="overflow-x-auto px-4 py-4">
                    <table class="crm-table min-w-full text-left text-sm">
                        <thead>
                            <tr>
                                <th>Sales</th>
                                <th>Assigned</th>
                                <th>Activated</th>
                                <th>Closed</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($salesReport as $row)
                                <tr>
                                    <td class="font-black text-[#07142b]">{{ $row['name'] }}</td>
                                    <td>{{ $row['assigned'] }}</td>
                                    <td>{{ $row['service_activated'] }}</td>
                                    <td>{{ $row['closed'] }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-[#64728a]">No sales users.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="crm-card overflow-hidden">
                <div class="border-b border-[#edf1f6] px-5 py-4">
                    <h2 class="text-lg font-black text-[#07142b]">RM Performance</h2>
                </div>
                <div class="overflow-x-auto px-4 py-4">
                    <table class="crm-table min-w-full text-left text-sm">
                        <thead>
                            <tr>
                                <th>RM</th>
                                <th>Assigned</th>
                                <th>Offered</th>
                                <th>Closed</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($rmReport as $row)
                                <tr>
                                    <td class="font-black text-[#07142b]">{{ $row['name'] }}</td>
                                    <td>{{ $row['assigned'] }}</td>
                                    <td>{{ $row['offered'] }}</td>
                                    <td>{{ $row['closed'] }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-[#64728a]">No RM users.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div id="activity" class="crm-card overflow-hidden">
            <div class="border-b border-[#edf1f6] px-5 py-4">
                <h2 class="text-lg font-black text-[#07142b]">Recent Activity</h2>
            </div>
            <div class="divide-y divide-[#edf1f6]">
                @forelse ($recentActivities as $activity)
                    <a href="{{ $activity->lead ? route('team.leads.show', $activity->lead) : '#' }}" class="flex gap-4 px-5 py-4 transition hover:bg-[#f8fbff]">
                        <div class="grid h-9 w-9 shrink-0 place-items-center rounded-lg bg-[#eafaf2] text-xs font-black text-[#0ca651]">A</div>
                        <div class="min-w-0">
                            <p class="text-sm font-black text-[#07142b]">{{ $activity->event }} @if($activity->lead) for {{ $activity->lead->name }} @endif</p>
                            <p class="mt-1 text-sm text-[#64728a]">{{ $activity->created_at->format('M d, Y h:i A') }} @if($activity->actor) by {{ $activity->actor->name }} @endif</p>
                        </div>
                    </a>
                @empty
                    <p class="px-5 py-8 text-center text-sm text-[#64728a]">No activity yet.</p>
                @endforelse
            </div>
        </div>
    </section>
@endsection
