@extends('layouts.team')

@section('title', 'RM Dashboard | Settle Pe CRM')
@section('page_title', 'RM CRM')
@section('page_subtitle', 'Track active settlement cases and closure progress.')
@section('search_action', route('team.rm'))

@section('content')
    @php
        $cards = [
            ['label' => 'Active Cases', 'value' => $metrics['total'], 'tone' => 'bg-[#eef2ff] text-[#3858ff]'],
            ['label' => 'Work Started', 'value' => $metrics['work_started'], 'tone' => 'bg-[#eafaf2] text-[#0ca651]'],
            ['label' => 'Settlement Offered', 'value' => $metrics['settlement_offered'], 'tone' => 'bg-[#fff4e8] text-[#c2410c]'],
            ['label' => 'Closed Cases', 'value' => $metrics['closed'], 'tone' => 'bg-[#f3e8ff] text-[#7e22ce]'],
        ];
        $statusClass = fn (?string $status) => match ($status) {
            'settlement_offered' => 'bg-[#fff3dc] text-[#b45309]',
            'case_closed_settle' => 'bg-[#dcfce7] text-[#15803d]',
            'case_closed_unsettle' => 'bg-[#fee2e2] text-[#b91c1c]',
            default => 'bg-[#eef4ff] text-[#1d4ed8]',
        };
    @endphp

    <section class="space-y-5">
        <div class="grid gap-4 md:grid-cols-4">
            @foreach ($cards as $card)
                <div class="crm-card p-5">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-black text-[#07142b]">{{ $card['label'] }}</p>
                        <span class="grid h-10 w-10 place-items-center rounded-full {{ $card['tone'] }}">●</span>
                    </div>
                    <p class="mt-4 text-3xl font-black text-[#07142b]">{{ $card['value'] }}</p>
                    <p class="mt-2 text-sm text-[#64728a]"><span class="font-bold text-[#0ca651]">↑ live</span> case queue</p>
                </div>
            @endforeach
        </div>

        <div class="crm-card p-4">
            <form method="GET" action="{{ route('team.rm') }}" class="grid gap-3 md:grid-cols-[1fr_240px_auto_auto]">
                <input name="search" value="{{ $search }}" placeholder="Search name, mobile, email, PAN" class="rounded-lg border border-[#dfe6f0] px-3 py-2 text-sm outline-none focus:border-[#4b63ff] focus:ring-2 focus:ring-[#4b63ff]/10">
                <select name="status" class="rounded-lg border border-[#dfe6f0] px-3 py-2 text-sm outline-none focus:border-[#4b63ff] focus:ring-2 focus:ring-[#4b63ff]/10">
                    <option value="">All RM statuses</option>
                    @foreach ($rmStatuses as $value => $label)
                        <option value="{{ $value }}" @selected($selectedStatus === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <button type="submit" class="rounded-lg bg-[#3858ff] px-4 py-2 text-sm font-bold text-white hover:bg-[#2442df]">Filter</button>
                <a href="{{ route('team.rm') }}" class="rounded-lg border border-[#dfe6f0] px-4 py-2 text-center text-sm font-bold text-[#526071] hover:bg-[#f8fbff]">Reset</a>
            </form>
        </div>

        <div class="crm-card overflow-hidden">
            <div class="flex items-center justify-between border-b border-[#edf1f6] px-5 py-4">
                <h2 class="text-lg font-black text-[#07142b]">Active Settlement Cases</h2>
                <span class="text-sm font-bold text-[#64728a]">{{ $leads->total() }} records</span>
            </div>
            <div class="overflow-x-auto px-4 py-4">
                <table class="crm-table min-w-full text-left text-sm">
                    <thead>
                        <tr>
                            <th>Case ID</th>
                            <th>Customer</th>
                            <th>Phone</th>
                            <th>Income</th>
                            <th>RM Stage</th>
                            <th>Sales Stage</th>
                            <th>CRIF</th>
                            <th>Remaining</th>
                            <th>Activated</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($leads as $lead)
                            <tr>
                                <td class="font-black text-[#07142b]">CASE-{{ str_pad((string) $lead->id, 4, '0', STR_PAD_LEFT) }}</td>
                                <td>
                                    <p class="font-black text-[#07142b]">{{ $lead->name }}</p>
                                    <p class="mt-1 text-xs text-[#64728a]">{{ $lead->email }}</p>
                                </td>
                                <td>{{ $lead->mobile }}</td>
                                <td>{{ $lead->income !== null ? 'Rs. '.number_format($lead->income) : '-' }}</td>
                                <td><span class="status-pill {{ $statusClass($lead->rm_status) }}">{{ $rmStatuses[$lead->rm_status] ?? 'Work started' }}</span></td>
                                <td>{{ \App\Models\User::SALES_STATUSES[$lead->sales_status] ?? '-' }}</td>
                                @php($credit = $creditSummaries[$lead->id] ?? ['score' => null, 'remaining' => 0])
                                <td class="font-bold">{{ $credit['score'] ?? '—' }}</td>
                                <td>Rs. {{ number_format($credit['remaining']) }}</td>
                                <td>{{ optional($lead->service_fee_paid_at)->format('M d, Y') }}</td>
                                <td class="text-right"><a href="{{ route('team.leads.show', $lead) }}" class="rounded-lg bg-[#07142b] px-3 py-2 text-xs font-bold text-white">Open</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="10" class="text-center text-[#64728a]">No RM cases found.</td></tr>
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
