@extends('layouts.team')

@section('title', 'Sales Dashboard | Settle Pe CRM')
@section('page_title', 'Sales CRM')
@section('page_subtitle', 'Convert signups to Rs. 99 consultations, then convert paid customers to RM recovery cases.')
@section('search_action', route('team.sales'))

@section('content')
    @php
        $formatRupees = fn (int $amount) => 'Rs. '.number_format($amount);
        $cards = [
            ['label' => 'Unpaid Signups', 'value' => $metrics['notPaid99'], 'note' => 'Send WhatsApp', 'tone' => 'bg-[#fff7ed] text-[#c2410c]'],
            ['label' => 'Paid Rs. 99', 'value' => $metrics['paidPendingRecovery'], 'note' => 'Call and convert', 'tone' => 'bg-[#eafaf2] text-[#0ca651]'],
            ['label' => 'Due Follow-ups', 'value' => $metrics['todayFollowUps'], 'note' => 'Due now', 'tone' => 'bg-[#fff1f0] text-[#b42318]'],
            ['label' => 'Converted to RM', 'value' => $metrics['serviceActivated'], 'note' => 'Recovery started', 'tone' => 'bg-[#eef2ff] text-[#3858ff]'],
        ];
        $statusClass = fn (?string $status) => match ($status) {
            'consultation_paid' => 'bg-[#e9f2ff] text-[#1d4ed8]',
            'service_fee_paid' => 'bg-[#dcfce7] text-[#15803d]',
            'settlement_pitched' => 'bg-[#f3e8ff] text-[#7e22ce]',
            'follow_up' => 'bg-[#fff7ed] text-[#c2410c]',
            'no_reply' => 'bg-[#f7f9fb] text-[#526071]',
            'not_interested' => 'bg-[#fee2e2] text-[#b91c1c]',
            default => 'bg-[#eef8f3] text-[#0d7a51]',
        };
        $priorityClass = fn (?string $priority) => match ($priority) {
            'urgent' => 'bg-[#fff1f0] text-[#b42318]',
            'high' => 'bg-[#fff7ed] text-[#c2410c]',
            'low' => 'bg-[#f7f9fb] text-[#64728a]',
            default => 'bg-[#eef2ff] text-[#3858ff]',
        };
    @endphp

    <section class="space-y-5">
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            @foreach ($cards as $card)
                <a href="{{ $card['label'] === 'Unpaid Signups' ? '#unpaid' : ($card['label'] === 'Paid Rs. 99' ? '#paid' : ($card['label'] === 'Converted to RM' ? '#converted' : '#followups')) }}" class="crm-card block p-4 transition hover:border-[#b9c7dc] hover:shadow-md">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-xs font-black uppercase tracking-wide text-[#64728a]">{{ $card['label'] }}</p>
                            <p class="mt-3 text-3xl font-black text-[#07142b]">{{ $card['value'] }}</p>
                        </div>
                        <span class="grid h-9 w-9 place-items-center rounded-lg text-xs font-black {{ $card['tone'] }}">{{ substr($card['label'], 0, 1) }}</span>
                    </div>
                    <p class="mt-3 text-sm font-semibold text-[#64728a]">{{ $card['note'] }}</p>
                </a>
            @endforeach
        </div>

        <div class="grid gap-5 xl:grid-cols-[1fr_360px]">
            <div class="space-y-5">
                <div id="unpaid" class="crm-card overflow-hidden">
                    <div class="flex flex-col gap-2 border-b border-[#edf1f6] px-5 py-4 md:flex-row md:items-center md:justify-between">
                        <div>
                            <h2 class="text-lg font-black text-[#07142b]">Unpaid Signups</h2>
                            <p class="mt-1 text-sm text-[#64728a]">Signed up, CRIF report fetched, Rs. 99 not paid.</p>
                        </div>
                        <span class="rounded-lg bg-[#fff7ed] px-3 py-1 text-sm font-bold text-[#c2410c]">{{ $unpaidLeads->count() }} visible</span>
                    </div>

                    <div class="divide-y divide-[#edf1f6]">
                        @forelse ($unpaidLeads as $lead)
                            @php
                                $message = 'Hi '.$lead->name.', you have started your Settle Pe loan settlement check. To speak with our expert and review your CRIF credit report, please complete the Rs. 99 consultation payment.';
                                $whatsAppUrl = 'https://wa.me/91'.preg_replace('/\D+/', '', (string) $lead->mobile).'?text='.rawurlencode($message);
                            @endphp
                            <div class="grid gap-4 px-5 py-4 lg:grid-cols-[1fr_auto] lg:items-center">
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <p class="font-black text-[#07142b]">CASE-{{ str_pad((string) $lead->id, 4, '0', STR_PAD_LEFT) }} - {{ $lead->name }}</p>
                                        <span class="status-pill {{ $statusClass($lead->sales_status) }}">{{ $salesStatuses[$lead->sales_status] ?? 'Signup' }}</span>
                                        <span class="status-pill {{ $priorityClass($lead->priority) }}">{{ $priorities[$lead->priority] ?? 'Normal' }}</span>
                                    </div>
                                    <div class="mt-3 grid gap-2 text-sm text-[#526071] md:grid-cols-3">
                                        <p><span class="font-bold text-[#07142b]">Phone:</span> +91 {{ $lead->mobile }}</p>
                                        <p><span class="font-bold text-[#07142b]">Income:</span> {{ $lead->income !== null ? $formatRupees($lead->income) : '-' }}</p>
                                        @php($credit = $creditSummaries[$lead->id] ?? ['score' => null, 'remaining' => 0, 'highest_dpd' => 0])
                                        <p><span class="font-bold text-[#07142b]">CRIF:</span> {{ $credit['score'] ?? 'Not available' }} / DPD {{ $credit['highest_dpd'] }}</p>
                                    </div>
                                </div>
                                <div class="flex flex-wrap gap-2 lg:justify-end">
                                    <a href="{{ $whatsAppUrl }}" target="_blank" rel="noopener" class="rounded-lg bg-[#0ca651] px-4 py-2 text-sm font-bold text-white hover:bg-[#088b43]">Send WhatsApp</a>
                                    <a href="{{ route('team.leads.show', $lead) }}" class="rounded-lg border border-[#dfe6f0] px-4 py-2 text-sm font-bold text-[#526071] hover:bg-[#f8fbff]">Open</a>
                                </div>
                            </div>
                        @empty
                            <p class="px-5 py-8 text-center text-sm text-[#64728a]">No unpaid signups assigned.</p>
                        @endforelse
                    </div>
                </div>

                <div id="paid" class="crm-card overflow-hidden">
                    <div class="flex flex-col gap-2 border-b border-[#edf1f6] px-5 py-4 md:flex-row md:items-center md:justify-between">
                        <div>
                            <h2 class="text-lg font-black text-[#07142b]">Paid Rs. 99 Leads</h2>
                            <p class="mt-1 text-sm text-[#64728a]">Paid consultation leads waiting for a sales call.</p>
                        </div>
                        <span class="rounded-lg bg-[#eafaf2] px-3 py-1 text-sm font-bold text-[#0ca651]">{{ $paidLeads->count() }} visible</span>
                    </div>

                    <div class="divide-y divide-[#edf1f6]">
                        @forelse ($paidLeads as $lead)
                            <div class="grid gap-4 px-5 py-4 xl:grid-cols-[1fr_360px]">
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <p class="font-black text-[#07142b]">CASE-{{ str_pad((string) $lead->id, 4, '0', STR_PAD_LEFT) }} - {{ $lead->name }}</p>
                                        <span class="status-pill {{ $statusClass($lead->sales_status) }}">{{ $salesStatuses[$lead->sales_status] ?? 'Paid' }}</span>
                                    </div>
                                    <div class="mt-3 grid gap-2 text-sm text-[#526071] md:grid-cols-3">
                                        <p><span class="font-bold text-[#07142b]">Phone:</span> +91 {{ $lead->mobile }}</p>
                                        @php($credit = $creditSummaries[$lead->id] ?? ['remaining' => 0])
                                        <p><span class="font-bold text-[#07142b]">Outstanding:</span> {{ $formatRupees($credit['remaining']) }}</p>
                                        <p><span class="font-bold text-[#07142b]">Paid:</span> {{ optional($lead->consultation_fee_paid_at)->format('M d, h:i A') }}</p>
                                    </div>
                                    @if ($lead->sales_notes)
                                        <p class="mt-3 rounded-lg bg-[#f8fbff] px-3 py-2 text-sm text-[#526071]">{{ $lead->sales_notes }}</p>
                                    @endif
                                    <div class="mt-3 flex flex-wrap gap-2">
                                        <a href="tel:+91{{ preg_replace('/\D+/', '', (string) $lead->mobile) }}" class="rounded-lg bg-[#07142b] px-4 py-2 text-sm font-bold text-white hover:bg-[#0b2545]">Call Now</a>
                                        <a href="{{ route('team.leads.show', $lead) }}" class="rounded-lg border border-[#dfe6f0] px-4 py-2 text-sm font-bold text-[#526071] hover:bg-[#f8fbff]">Open Details</a>
                                    </div>
                                </div>

                                <form method="POST" action="{{ route('team.leads.sales', $lead) }}" class="rounded-lg border border-[#dfe6f0] p-3">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="sales_status" value="service_fee_paid">
                                    <input type="hidden" name="call_disposition" value="converted">
                                    <input type="hidden" name="priority" value="{{ $lead->priority ?? 'normal' }}">
                                    <input type="hidden" name="mark_service_fee_paid" value="1">
                                    <label class="block text-xs font-black uppercase tracking-wide text-[#64728a]">Recovery handoff note</label>
                                    <textarea name="sales_notes" rows="3" placeholder="Add the agreed handoff details" class="mt-2 w-full rounded-lg border border-[#dfe6f0] px-3 py-2 text-sm outline-none focus:border-[#4b63ff] focus:ring-2 focus:ring-[#4b63ff]/10">{{ old('sales_notes') }}</textarea>
                                    <button type="submit" class="mt-3 w-full rounded-lg bg-[#3858ff] px-4 py-2 text-sm font-bold text-white hover:bg-[#2442df]">Send to RM</button>
                                </form>
                            </div>
                        @empty
                            <p class="px-5 py-8 text-center text-sm text-[#64728a]">No paid leads waiting for sales call.</p>
                        @endforelse
                    </div>
                </div>

                <div id="converted" class="crm-card overflow-hidden">
                    <div class="flex flex-col gap-2 border-b border-[#edf1f6] px-5 py-4 md:flex-row md:items-center md:justify-between">
                        <div>
                            <h2 class="text-lg font-black text-[#07142b]">Converted to RM</h2>
                            <p class="mt-1 text-sm text-[#64728a]">Settlement service started. RM can access the profile, CRIF report, documents, and activity.</p>
                        </div>
                        <span class="rounded-lg bg-[#eef2ff] px-3 py-1 text-sm font-bold text-[#3858ff]">{{ $convertedLeads->count() }} recent</span>
                    </div>

                    <div class="overflow-x-auto px-4 py-4">
                        <table class="crm-table min-w-full text-left text-sm">
                            <thead>
                                <tr>
                                    <th>Case</th>
                                    <th>Customer</th>
                                    <th>RM Owner</th>
                                    <th>Converted</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($convertedLeads as $lead)
                                    <tr>
                                        <td class="font-black text-[#07142b]">CASE-{{ str_pad((string) $lead->id, 4, '0', STR_PAD_LEFT) }}</td>
                                        <td>
                                            <p class="font-black text-[#07142b]">{{ $lead->name }}</p>
                                            <p class="mt-1 text-xs text-[#64728a]">+91 {{ $lead->mobile }}</p>
                                        </td>
                                        <td>{{ $lead->assignedRm?->name ?? 'Awaiting RM' }}</td>
                                        <td>{{ optional($lead->service_fee_paid_at)->format('M d, h:i A') }}</td>
                                        <td class="text-right"><a href="{{ route('team.leads.show', $lead) }}" class="rounded-lg bg-[#07142b] px-3 py-2 text-xs font-bold text-white">Open</a></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center text-[#64728a]">No converted leads yet.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <aside class="space-y-5">
                <div id="followups" class="crm-card overflow-hidden">
                    <div class="border-b border-[#edf1f6] px-5 py-4">
                        <h2 class="text-lg font-black text-[#07142b]">Due Follow-ups</h2>
                    </div>
                    <div class="divide-y divide-[#edf1f6]">
                        @forelse ($dueFollowUps as $followUp)
                            <a href="{{ route('team.leads.show', $followUp) }}" class="block px-5 py-4 transition hover:bg-[#f8fbff]">
                                <div class="flex items-start justify-between gap-4">
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-black text-[#07142b]">{{ $followUp->name }}</p>
                                        <p class="mt-1 truncate text-sm text-[#64728a]">{{ $followUp->sales_notes ?: 'Follow up with customer' }}</p>
                                    </div>
                                    <span class="shrink-0 rounded-lg bg-[#fff1f0] px-3 py-1 text-xs font-bold text-[#b42318]">{{ optional($followUp->follow_up_at)->format('h:i A') }}</span>
                                </div>
                            </a>
                        @empty
                            <p class="px-5 py-8 text-center text-sm text-[#64728a]">No due follow-ups.</p>
                        @endforelse
                    </div>
                </div>

                <div class="crm-card p-5">
                    <h2 class="text-lg font-black text-[#07142b]">Lead Stage Counts</h2>
                    <div class="mt-4 space-y-3 text-sm">
                        @foreach ($stageCounts as $status => $count)
                            <a href="{{ route('team.sales', ['status' => $status]) }}" class="flex items-center justify-between rounded-lg border border-[#edf1f6] px-3 py-2 transition hover:bg-[#f8fbff]">
                                <span class="font-semibold text-[#526071]">{{ $salesStatuses[$status] ?? $status }}</span>
                                <span class="font-black text-[#07142b]">{{ $count }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            </aside>
        </div>

        <div class="crm-card overflow-hidden">
            <div class="flex flex-col gap-4 border-b border-[#edf1f6] px-5 py-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h2 class="text-lg font-black text-[#07142b]">All Lead Records</h2>
                    <p class="mt-1 text-sm text-[#64728a]">{{ $leads->total() }} total records in this view.</p>
                </div>
                <form method="GET" action="{{ route('team.sales') }}" class="grid gap-2 sm:grid-cols-[1fr_190px_160px_auto_auto]">
                    <input name="search" value="{{ $search }}" placeholder="Name, mobile, email, PAN" class="rounded-lg border border-[#dfe6f0] px-3 py-2 text-sm outline-none focus:border-[#4b63ff] focus:ring-2 focus:ring-[#4b63ff]/10">
                    <select name="status" class="rounded-lg border border-[#dfe6f0] px-3 py-2 text-sm outline-none focus:border-[#4b63ff] focus:ring-2 focus:ring-[#4b63ff]/10">
                        <option value="">All stages</option>
                        @foreach ($salesStatuses as $value => $label)
                            <option value="{{ $value }}" @selected($selectedStatus === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <select name="priority" class="rounded-lg border border-[#dfe6f0] px-3 py-2 text-sm outline-none focus:border-[#4b63ff] focus:ring-2 focus:ring-[#4b63ff]/10">
                        <option value="">All priorities</option>
                        @foreach ($priorities as $value => $label)
                            <option value="{{ $value }}" @selected($selectedPriority === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="rounded-lg bg-[#3858ff] px-4 py-2 text-sm font-bold text-white hover:bg-[#2442df]">Filter</button>
                    <a href="{{ route('team.sales') }}" class="rounded-lg border border-[#dfe6f0] px-4 py-2 text-center text-sm font-bold text-[#526071] hover:bg-[#f8fbff]">Reset</a>
                </form>
            </div>

            <div class="overflow-x-auto px-4 py-4">
                <table class="crm-table min-w-full text-left text-sm">
                    <thead>
                        <tr>
                            <th>Case</th>
                            <th>Customer</th>
                            <th>Payment</th>
                            <th>Stage</th>
                            <th>Follow-up</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($leads as $lead)
                            <tr>
                                <td class="font-black text-[#07142b]">CASE-{{ str_pad((string) $lead->id, 4, '0', STR_PAD_LEFT) }}</td>
                                <td>
                                    <p class="font-black text-[#07142b]">{{ $lead->name }}</p>
                                    <p class="mt-1 text-xs text-[#64728a]">+91 {{ $lead->mobile }} | {{ $lead->email }}</p>
                                </td>
                                <td>
                                    @if ($lead->service_fee_paid_at)
                                        <span class="status-pill bg-[#eef2ff] text-[#3858ff]">Sent to RM</span>
                                    @elseif ($lead->consultation_fee_paid_at)
                                        <span class="status-pill bg-[#dcfce7] text-[#15803d]">Rs. 99 paid</span>
                                    @else
                                        <span class="status-pill bg-[#fff7ed] text-[#c2410c]">Unpaid</span>
                                    @endif
                                </td>
                                <td><span class="status-pill {{ $statusClass($lead->sales_status) }}">{{ $salesStatuses[$lead->sales_status] ?? 'Lead Created' }}</span></td>
                                <td>{{ optional($lead->follow_up_at)->format('M d, h:i A') ?? '-' }}</td>
                                <td class="text-right"><a href="{{ route('team.leads.show', $lead) }}" class="rounded-lg bg-[#07142b] px-3 py-2 text-xs font-bold text-white">Open</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-[#64728a]">No sales leads match this view.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($leads->hasPages())
                <div class="border-t border-[#edf1f6] px-5 py-4">{{ $leads->links() }}</div>
            @endif
        </div>
    </section>
@endsection
