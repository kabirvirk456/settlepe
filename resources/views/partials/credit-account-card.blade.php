@php
    $statusClasses = match ($account['status']) {
        'overdue' => 'border-[#efaca7] bg-[#fff5f4] text-[#c62f27]',
        'closed' => 'border-[#d5dae1] bg-[#f0f2f5] text-[#4f5b6b]',
        default => 'border-[#b9dfca] bg-[#edf8f2] text-[#0d7a51]',
    };
@endphp
<details data-account-item data-status="{{ $account['status'] }}" data-lender="{{ mb_strtolower($account['bank_name']) }}" data-search="{{ mb_strtolower($account['bank_name'].' '.$account['product']) }}" data-outstanding="{{ $account['remaining_amount'] }}" data-dpd="{{ $account['dpd'] }}" class="account-details group rounded-xl border border-[#d9e1ea] bg-white shadow-sm" @if ($index === 0) open @endif>
    <summary class="flex min-h-[76px] cursor-pointer list-none items-center gap-3 px-4 py-3">
        <span class="grid h-10 w-10 shrink-0 place-items-center rounded-lg bg-[#10223f] text-sm font-semibold text-white">{{ mb_strtoupper(mb_substr($account['bank_name'], 0, 1)) }}</span>
        <div class="min-w-0 flex-1">
            <div class="flex flex-wrap items-center gap-2"><p class="truncate font-semibold text-[#111827]">{{ $account['bank_name'] }}</p><span class="rounded-md border px-2 py-0.5 text-[11px] font-semibold capitalize {{ $statusClasses }}">{{ $account['status'] }}</span></div>
            <p class="mt-0.5 truncate text-xs text-[#687386]">{{ $account['product'] }}</p>
        </div>
        <div class="shrink-0 text-right"><p class="text-sm font-semibold text-[#111827]">{{ $formatRupees($account['remaining_amount']) }}</p><p class="mt-1 text-xs font-semibold {{ $account['dpd'] > 0 ? 'text-[#c62f27]' : 'text-[#0d7a51]' }}">{{ $account['dpd'] }} DPD</p></div>
        <span class="account-chevron text-lg text-[#687386] transition-transform group-open:rotate-180">⌄</span>
    </summary>
    <div class="grid grid-cols-2 gap-px border-t border-[#e8edf2] bg-[#e8edf2] sm:grid-cols-3">
        <div class="bg-[#fafbfd] p-3"><p class="text-[11px] text-[#687386]">Principal amount</p><p class="mt-1 text-sm font-semibold text-[#111827]">{{ $formatRupees($account['principal_amount']) }}</p></div>
        <div class="bg-[#fafbfd] p-3"><p class="text-[11px] text-[#687386]">Account status</p><p class="mt-1 text-sm font-semibold capitalize text-[#111827]">{{ $account['status'] }}</p></div>
        <div class="col-span-2 bg-[#fafbfd] p-3 sm:col-span-1"><p class="text-[11px] text-[#687386]">Opened on</p><p class="mt-1 text-sm font-semibold text-[#111827]">{{ $account['opened_on'] ?: 'Not provided' }}</p></div>
    </div>
</details>
