@php
    $statusClasses = match ($account['status']) {
        'overdue' => 'border-[#efaca7] bg-[#fff5f4] text-[#c62f27]',
        'closed' => 'border-[#d5dae1] bg-[#f0f2f5] text-[#4f5b6b]',
        default => 'border-[#b9dfca] bg-[#edf8f2] text-[#0d7a51]',
    };
@endphp
<tr data-account-item data-account-key="{{ $index }}" data-status="{{ $account['status'] }}" data-lender="{{ mb_strtolower($account['bank_name']) }}" data-search="{{ mb_strtolower($account['bank_name'].' '.$account['product']) }}" data-outstanding="{{ $account['remaining_amount'] }}" data-dpd="{{ $account['dpd'] }}" class="hover:bg-[#fafbfd]">
    <td class="px-4 py-3">
        <div class="flex items-center gap-3">
            <span class="grid h-9 w-9 shrink-0 place-items-center rounded-lg bg-[#10223f] text-xs font-semibold text-white">{{ mb_strtoupper(mb_substr($account['bank_name'], 0, 1)) }}</span>
            <div class="min-w-0"><p class="truncate font-semibold text-[#111827]">{{ $account['bank_name'] }}</p><p class="truncate text-xs text-[#687386]">{{ $account['product'] }}</p></div>
        </div>
    </td>
    <td class="px-4 py-3"><span class="inline-flex rounded-md border px-2.5 py-1 text-xs font-semibold capitalize {{ $statusClasses }}">{{ $account['status'] }}</span></td>
    <td class="px-4 py-3 text-right font-semibold text-[#111827]">{{ $formatRupees($account['remaining_amount']) }}</td>
    <td class="px-4 py-3 text-right font-semibold {{ $account['dpd'] > 0 ? 'text-[#c62f27]' : 'text-[#0d7a51]' }}">{{ $account['dpd'] }}</td>
    <td class="px-4 py-3 text-right text-[#4f5b6b]">{{ $account['opened_on'] ?: '—' }}</td>
    <td class="px-4 py-3 text-center">
        <button type="button" data-expand-account="{{ $index }}" aria-expanded="false" class="grid h-9 w-9 place-items-center rounded-md text-xl text-[#687386] transition hover:bg-[#eef2f6] hover:text-[#10223f]" aria-label="Show account details">⌄</button>
    </td>
</tr>
<tr data-account-detail="{{ $index }}" class="hidden bg-[#f8fafb]">
    <td colspan="6" class="border-t border-[#e8edf2] px-4 py-4">
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div><p class="text-[11px] font-medium uppercase tracking-wide text-[#7b8797]">Principal amount</p><p class="mt-1 text-sm font-semibold text-[#111827]">{{ $formatRupees($account['principal_amount']) }}</p></div>
            <div><p class="text-[11px] font-medium uppercase tracking-wide text-[#7b8797]">Current outstanding</p><p class="mt-1 text-sm font-semibold text-[#111827]">{{ $formatRupees($account['remaining_amount']) }}</p></div>
            <div><p class="text-[11px] font-medium uppercase tracking-wide text-[#7b8797]">Account status</p><p class="mt-1 text-sm font-semibold capitalize text-[#111827]">{{ $account['status'] }}</p></div>
            <div><p class="text-[11px] font-medium uppercase tracking-wide text-[#7b8797]">Opened on</p><p class="mt-1 text-sm font-semibold text-[#111827]">{{ $account['opened_on'] ?: 'Not provided' }}</p></div>
        </div>
    </td>
</tr>
