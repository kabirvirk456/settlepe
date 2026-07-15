<article class="rounded-xl border border-[#d9e1ea] bg-white p-4 shadow-sm">
    <div class="flex items-center gap-3">
        <span class="grid h-9 w-9 place-items-center rounded-lg bg-[#fff0ef] text-lg text-[#c62f27]">!</span>
        <div>
            <h2 class="font-semibold text-[#111827]">Needs attention</h2>
            <p class="text-xs text-[#687386]">Overdue accounts, highest DPD first</p>
        </div>
    </div>

    <div class="report-scrollbar mt-3 max-h-[292px] space-y-1 overflow-y-auto pr-2">
        @forelse ($attentionAccounts as $account)
            <div class="flex items-start gap-3 border-b border-[#e8edf2] py-3 last:border-b-0">
                <span class="grid h-10 w-10 shrink-0 place-items-center rounded-lg bg-[#10223f] text-sm font-semibold text-white">{{ mb_strtoupper(mb_substr($account['bank_name'], 0, 1)) }}</span>
                <div class="min-w-0 flex-1">
                    <div class="flex items-start justify-between gap-2">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold text-[#111827]">{{ $account['bank_name'] }}</p>
                            <p class="truncate text-xs text-[#687386]">{{ $account['product'] }}</p>
                        </div>
                        <span class="shrink-0 rounded-md border border-[#efaca7] bg-[#fff5f4] px-2 py-1 text-[11px] font-semibold text-[#c62f27]">{{ $account['dpd'] }} days</span>
                    </div>
                    <p class="mt-1 text-xs text-[#687386]">Outstanding: <span class="font-semibold text-[#c62f27]">{{ $formatRupees($account['remaining_amount']) }}</span></p>
                </div>
            </div>
        @empty
            <div class="py-8 text-center text-sm text-[#687386]">No overdue accounts found.</div>
        @endforelse
    </div>
</article>
