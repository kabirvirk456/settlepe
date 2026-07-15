@extends('layouts.auth')

@section('title', 'Your CRIF Credit Report | Settle Pe')
@section('panel_width', 'w-full max-w-[1480px]')

@section('content')
    @php
        $formatRupees = fn (int $amount) => 'Rs. '.number_format($amount);
        $totalAccounts = count($accounts);
        $scorePercent = $cibilScore !== null ? min(100, max(0, ($cibilScore - 300) / 6)) : 0;
        $scoreLabel = match (true) {
            $cibilScore === null => 'Not provided',
            $cibilScore >= 750 => 'Excellent',
            $cibilScore >= 700 => 'Good',
            $cibilScore >= 650 => 'Fair',
            default => 'Needs attention',
        };
        $attentionAccounts = collect($accounts)
            ->where('status', 'overdue')
            ->sortByDesc('dpd')
            ->values();
        $lenders = collect($accounts)->pluck('bank_name')->filter()->unique()->sort()->values();
        $reportId = $report?->report_id ?: data_get($report?->report_response, 'data.credit_report.HEADER.REPORT-ID');
        $hasConsulted = (bool) $user->consultation_fee_paid_at;
    @endphp

    <style>
        .report-scrollbar { scrollbar-color: #70809a #eef1f4; scrollbar-width: thin; }
        .report-scrollbar::-webkit-scrollbar { width: 6px; height: 6px; }
        .report-scrollbar::-webkit-scrollbar-track { background: #eef1f4; border-radius: 999px; }
        .report-scrollbar::-webkit-scrollbar-thumb { background: #70809a; border-radius: 999px; }
        .account-details summary::-webkit-details-marker { display: none; }
    </style>

    <section class="space-y-5 {{ $hasConsulted ? '' : 'pb-24 md:pb-0' }}" id="creditReportDashboard">
        <header class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[#9a6b18]">Credit bureau summary</p>
                <h1 class="mt-2 text-3xl font-semibold tracking-tight text-[#10223f] md:text-4xl">Your CRIF credit report</h1>
                <div class="mt-2 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-[#687386] md:text-sm">
                    <span>Fetched {{ $report?->completed_at?->format('d M Y, h:i A') ?? 'recently' }}</span>
                    @if ($reportId)
                        <span class="hidden text-[#c8ced7] sm:inline">|</span>
                        <span>Report ID: {{ $reportId }}</span>
                    @endif
                </div>
            </div>

            @if ($hasConsulted)
                <span class="inline-flex w-fit items-center rounded-md bg-[#eaf7f0] px-4 py-2.5 text-sm font-semibold text-[#0d7a51]">Consultation paid</span>
            @else
                <div>
                    <button type="button" data-consultation-button class="w-full rounded-md bg-[#f4c877] px-5 py-3 text-sm font-bold text-[#10223f] shadow-sm ring-1 ring-[#d9a63f] transition hover:bg-[#f7d692] disabled:cursor-wait disabled:opacity-70 sm:w-auto">
                        Get expert review — Rs. 99
                    </button>
                </div>
            @endif
        </header>

        <div class="grid gap-4 xl:grid-cols-[1.45fr_repeat(4,minmax(0,1fr))]">
            <article class="rounded-xl border border-[#d9e1ea] bg-white p-5 shadow-sm">
                <div class="flex items-center gap-5">
                    <div class="relative grid h-28 w-28 shrink-0 place-items-center rounded-full" style="background: conic-gradient(#0d7a51 {{ $scorePercent }}%, #e6ebf0 0)">
                        <div class="grid h-[88px] w-[88px] place-items-center rounded-full bg-white text-center">
                            <div>
                                <p class="text-3xl font-semibold text-[#10223f]">{{ $cibilScore ?? '—' }}</p>
                                <p class="text-xs font-semibold text-[#0d7a51]">{{ $scoreLabel }}</p>
                            </div>
                        </div>
                    </div>
                    <div>
                        <p class="text-lg font-semibold text-[#111827]">Your credit score</p>
                        <p class="mt-1 text-sm text-[#687386]">CRIF score range</p>
                        <p class="mt-2 text-sm font-medium text-[#111827]">300–900</p>
                    </div>
                </div>
            </article>

            <div class="report-scrollbar -mx-4 flex gap-3 overflow-x-auto px-4 pb-1 xl:contents">
                @foreach ([
                    ['Total accounts', $totalAccounts, 'Across all statuses', 'bg-[#f7f3e8] text-[#9a6b18]', '▣'],
                    ['Active accounts', $activeAccountsCount, 'Current and open', 'bg-[#eaf7f0] text-[#0d7a51]', '✓'],
                    ['Total outstanding', $formatRupees($totalRemaining), 'Across all accounts', 'bg-[#f7f3e8] text-[#9a6b18]', '₹'],
                    ['Overdue accounts', $overdueAccountsCount, 'Need attention', 'bg-[#fff0ef] text-[#c62f27]', '!'],
                ] as [$label, $value, $caption, $iconClass, $icon])
                    <article class="min-w-[176px] flex-1 rounded-xl border border-[#d9e1ea] bg-white p-5 shadow-sm xl:min-w-0">
                        <div class="grid h-10 w-10 place-items-center rounded-lg text-lg font-semibold {{ $iconClass }}">{{ $icon }}</div>
                        <p class="mt-4 text-xs font-medium text-[#687386]">{{ $label }}</p>
                        <p class="mt-2 text-2xl font-semibold tracking-tight text-[#111827] {{ $label === 'Total outstanding' ? 'whitespace-nowrap text-xl' : '' }}">{{ $value }}</p>
                        <p class="mt-1 text-xs text-[#8a94a3]">{{ $caption }}</p>
                    </article>
                @endforeach
            </div>
        </div>

        @unless ($hasConsulted)
            <aside class="overflow-hidden rounded-xl bg-[#10223f] shadow-md ring-1 ring-[#10223f]">
                <div class="flex flex-col gap-5 px-5 py-5 sm:flex-row sm:items-center sm:justify-between md:px-7">
                    <div class="flex items-start gap-4">
                        <span class="grid h-11 w-11 shrink-0 place-items-center rounded-full bg-[#f4c877] text-xl text-[#10223f]">✓</span>
                        <div>
                            <p class="text-xs font-bold uppercase tracking-[0.14em] text-[#f4c877]">Turn this report into an action plan</p>
                            <h2 class="mt-1 text-xl font-semibold text-white">Get a 1-on-1 expert review for just Rs. 99</h2>
                            <p class="mt-1 text-sm leading-6 text-white/70">Understand which accounts need attention, your settlement options, and the safest next steps.</p>
                        </div>
                    </div>
                    <div class="shrink-0">
                        <button type="button" data-consultation-button class="w-full rounded-lg bg-[#f4c877] px-6 py-3.5 text-sm font-bold text-[#10223f] shadow-sm transition hover:bg-[#f7d692] disabled:cursor-wait disabled:opacity-70 sm:w-auto">
                            Book consultation — Rs. 99
                        </button>
                        <p class="mt-2 text-center text-[11px] text-white/60">One-time expert review</p>
                    </div>
                </div>
            </aside>
        @endunless

        <div class="xl:hidden">
            @include('partials.credit-attention', ['attentionAccounts' => $attentionAccounts, 'formatRupees' => $formatRupees])
        </div>

        <section class="rounded-xl border border-[#d9e1ea] bg-white p-4 shadow-sm md:p-5">
            <div class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_330px]">
                <div class="min-w-0">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <h2 class="text-xl font-semibold text-[#111827]">Credit accounts <span class="text-[#687386]">({{ $totalAccounts }})</span></h2>
                            <p class="mt-1 text-xs text-[#687386]">Use filters to focus on the accounts that matter.</p>
                        </div>
                    </div>

                    <div class="report-scrollbar mt-4 flex overflow-x-auto rounded-lg border border-[#d9e1ea]" role="tablist" aria-label="Filter accounts by status">
                        @foreach ([
                            'all' => ['All', $totalAccounts],
                            'active' => ['Active', $activeAccountsCount],
                            'overdue' => ['Overdue', $overdueAccountsCount],
                            'closed' => ['Closed', $closedAccountsCount],
                        ] as $value => [$label, $count])
                            <button type="button" data-status-tab="{{ $value }}" class="status-tab min-w-[112px] flex-1 border-r border-[#d9e1ea] px-4 py-3 text-sm font-medium text-[#4f5b6b] last:border-r-0 {{ $value === 'all' ? 'bg-[#10223f] text-white' : 'bg-white' }}">
                                {{ $label }} ({{ $count }})
                            </button>
                        @endforeach
                    </div>

                    <div class="mt-4 grid gap-3 md:grid-cols-[minmax(0,1fr)_180px_220px]">
                        <label class="relative block">
                            <span class="pointer-events-none absolute inset-y-0 left-3 grid place-items-center text-[#687386]">⌕</span>
                            <input id="accountSearch" type="search" placeholder="Search accounts" class="h-11 w-full rounded-lg border border-[#cfd8e3] bg-white pl-10 pr-3 text-sm outline-none transition focus:border-[#10223f] focus:ring-2 focus:ring-[#10223f]/10">
                        </label>

                        <select id="lenderFilter" class="hidden h-11 rounded-lg border border-[#cfd8e3] bg-white px-3 text-sm text-[#374151] outline-none focus:border-[#10223f] md:block">
                            <option value="all">All lenders</option>
                            @foreach ($lenders as $lender)<option value="{{ mb_strtolower($lender) }}">{{ $lender }}</option>@endforeach
                        </select>

                        <select id="accountSort" class="hidden h-11 rounded-lg border border-[#cfd8e3] bg-white px-3 text-sm text-[#374151] outline-none focus:border-[#10223f] md:block">
                            <option value="outstanding_desc">Outstanding: high to low</option>
                            <option value="dpd_desc">DPD: high to low</option>
                            <option value="lender_asc">Lender: A to Z</option>
                        </select>

                        <button id="mobileFiltersButton" type="button" class="h-11 rounded-lg border border-[#10223f] px-4 text-sm font-semibold text-[#10223f] md:hidden">Filter & sort</button>
                    </div>

                    <div id="mobileFiltersPanel" class="mt-3 hidden grid-cols-2 gap-3 rounded-lg border border-[#d9e1ea] bg-[#f7f9fb] p-3 md:hidden">
                        <select id="mobileLenderFilter" class="h-11 min-w-0 rounded-lg border border-[#cfd8e3] bg-white px-2 text-sm">
                            <option value="all">All lenders</option>
                            @foreach ($lenders as $lender)<option value="{{ mb_strtolower($lender) }}">{{ $lender }}</option>@endforeach
                        </select>
                        <select id="mobileAccountSort" class="h-11 min-w-0 rounded-lg border border-[#cfd8e3] bg-white px-2 text-sm">
                            <option value="outstanding_desc">Highest balance</option>
                            <option value="dpd_desc">Highest DPD</option>
                            <option value="lender_asc">Lender A–Z</option>
                        </select>
                    </div>

                    @if ($accounts)
                        <div id="desktopAccounts" class="mt-4 hidden overflow-hidden rounded-lg border border-[#d9e1ea] lg:block">
                            <table class="w-full table-fixed text-left text-sm">
                                <thead class="bg-[#f7f3e8] text-xs font-semibold text-[#4f5b6b]">
                                    <tr>
                                        <th class="w-[29%] px-4 py-3">Lender & product</th>
                                        <th class="w-[13%] px-4 py-3">Status</th>
                                        <th class="w-[17%] px-4 py-3 text-right">Outstanding</th>
                                        <th class="w-[12%] px-4 py-3 text-right">DPD</th>
                                        <th class="w-[22%] px-4 py-3 text-right">Opened on</th>
                                        <th class="w-[7%] px-4 py-3 text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-[#e8edf2]" data-account-list>
                                    @foreach ($accounts as $index => $account)
                                        @include('partials.credit-account-row', ['account' => $account, 'index' => $index, 'formatRupees' => $formatRupees])
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div id="mobileAccounts" class="mt-4 space-y-3 lg:hidden" data-account-list>
                            @foreach ($accounts as $index => $account)
                                @include('partials.credit-account-card', ['account' => $account, 'index' => $index, 'formatRupees' => $formatRupees])
                            @endforeach
                        </div>

                        <div class="mt-4 flex flex-col items-center justify-between gap-3 sm:flex-row">
                            <p id="accountResultsSummary" class="text-xs text-[#687386]">Showing up to 10 of {{ $totalAccounts }} accounts</p>
                            <div id="desktopPagination" class="hidden items-center gap-1.5 lg:flex"></div>
                            <button id="loadMoreAccounts" type="button" class="w-full rounded-lg bg-[#10223f] px-5 py-3 text-sm font-semibold text-white lg:hidden">Load more accounts</button>
                        </div>
                    @else
                        <div class="mt-5 rounded-lg border border-dashed border-[#cfd8e3] bg-[#f8fafb] px-5 py-10 text-center">
                            <p class="font-semibold text-[#111827]">No account-level data was returned</p>
                            <p class="mt-2 text-sm text-[#687386]">The CRIF score is available, but there are no recognizable tradelines to display.</p>
                        </div>
                    @endif
                </div>

                <aside class="hidden xl:block">
                    <div class="sticky top-5">
                        @include('partials.credit-attention', ['attentionAccounts' => $attentionAccounts, 'formatRupees' => $formatRupees])
                    </div>
                </aside>
            </div>
        </section>
    </section>

    @unless ($hasConsulted)
        <div class="fixed inset-x-0 bottom-0 z-50 border-t border-[#d9a63f] bg-[#10223f] px-4 py-3 shadow-[0_-8px_24px_rgba(16,34,63,0.2)] md:hidden">
            <div class="mx-auto flex max-w-md items-center justify-between gap-3">
                <div class="min-w-0">
                    <p class="truncate text-xs font-semibold text-white">Expert CRIF review</p>
                    <p class="mt-0.5 text-lg font-bold text-[#f4c877]">Rs. 99 only</p>
                </div>
                <button type="button" data-consultation-button class="min-h-11 shrink-0 rounded-lg bg-[#f4c877] px-5 text-sm font-bold text-[#10223f] disabled:cursor-wait disabled:opacity-70">Book now</button>
            </div>
        </div>
    @endunless

    @unless ($hasConsulted)
        <div id="paymentMessage" class="fixed left-1/2 top-5 z-[70] hidden w-[calc(100%-2rem)] max-w-md -translate-x-1/2 rounded-lg border px-4 py-3 text-sm font-semibold shadow-lg" role="alert"></div>
        <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const buttons = [...document.querySelectorAll('[data-consultation-button]')];
                const message = document.getElementById('paymentMessage');
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
                let isOpening = false;

                const showMessage = (text, type = 'error') => {
                    if (!message) return;
                    message.textContent = text;
                    message.className = `fixed left-1/2 top-5 z-[70] w-[calc(100%-2rem)] max-w-md -translate-x-1/2 rounded-lg border px-4 py-3 text-sm font-semibold shadow-lg ${type === 'success' ? 'border-[#b9dfca] bg-[#edf8f2] text-[#0d7a51]' : 'border-[#efaca7] bg-[#fff5f4] text-[#b42318]'}`;
                };

                const setLoading = (loading) => {
                    buttons.forEach(button => {
                        if (!button.dataset.originalText) button.dataset.originalText = button.textContent.trim();
                        button.disabled = loading;
                        button.textContent = loading ? 'Preparing secure payment…' : button.dataset.originalText;
                    });
                };

                const postJson = async (url, payload = {}) => {
                    const response = await fetch(url, {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                        body: JSON.stringify(payload),
                    });
                    const data = await response.json().catch(() => ({}));
                    if (!response.ok) throw new Error(data.message || 'Payment could not be processed. Please try again.');
                    return data;
                };

                const openCheckout = async () => {
                    if (isOpening) return;
                    isOpening = true;
                    setLoading(true);

                    try {
                        if (typeof window.Razorpay !== 'function') {
                            throw new Error('Razorpay Checkout could not load. Check your connection and try again.');
                        }

                        const order = await postJson(@json(route('consultation.orders.store')));
                        if (order.already_paid) {
                            window.location.assign(order.redirect_url);
                            return;
                        }

                        const checkout = new window.Razorpay({
                            key: order.key,
                            amount: order.amount,
                            currency: order.currency,
                            name: order.name,
                            description: order.description,
                            order_id: order.order_id,
                            prefill: order.prefill,
                            theme: order.theme,
                            retry: { enabled: true },
                            modal: {
                                backdropclose: false,
                                ondismiss: () => {
                                    isOpening = false;
                                    setLoading(false);
                                },
                            },
                            handler: async (response) => {
                                try {
                                    showMessage('Verifying your payment…', 'success');
                                    const result = await postJson(@json(route('consultation.payments.verify')), response);
                                    showMessage('Payment verified. Opening your consultation dashboard…', 'success');
                                    window.location.assign(result.redirect_url);
                                } catch (error) {
                                    showMessage(error.message);
                                    isOpening = false;
                                    setLoading(false);
                                }
                            },
                        });

                        checkout.on('payment.failed', (response) => {
                            showMessage(response.error?.description || 'Payment failed. Please try another payment method.');
                            isOpening = false;
                            setLoading(false);
                        });
                        checkout.open();
                    } catch (error) {
                        showMessage(error.message);
                        isOpening = false;
                        setLoading(false);
                    }
                };

                buttons.forEach(button => button.addEventListener('click', openCheckout));
            });
        </script>
    @endunless

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const dashboard = document.getElementById('creditReportDashboard');
            if (!dashboard) return;

            const state = { status: 'all', lender: 'all', search: '', sort: 'outstanding_desc', page: 1, mobileLimit: 10 };
            const perPage = 10;
            const search = document.getElementById('accountSearch');
            const lender = document.getElementById('lenderFilter');
            const sort = document.getElementById('accountSort');
            const mobileLender = document.getElementById('mobileLenderFilter');
            const mobileSort = document.getElementById('mobileAccountSort');
            const summary = document.getElementById('accountResultsSummary');

            const filteredItems = (container) => {
                const items = [...container.querySelectorAll('[data-account-item]')];
                return items.filter((item) => {
                    const statusMatches = state.status === 'all' || item.dataset.status === state.status;
                    const lenderMatches = state.lender === 'all' || item.dataset.lender === state.lender;
                    const searchMatches = !state.search || item.dataset.search.includes(state.search);
                    return statusMatches && lenderMatches && searchMatches;
                }).sort((a, b) => {
                    if (state.sort === 'dpd_desc') return Number(b.dataset.dpd) - Number(a.dataset.dpd);
                    if (state.sort === 'lender_asc') return a.dataset.lender.localeCompare(b.dataset.lender);
                    return Number(b.dataset.outstanding) - Number(a.dataset.outstanding);
                });
            };

            const render = () => {
                const desktop = document.getElementById('desktopAccounts');
                const mobile = document.getElementById('mobileAccounts');
                let resultCount = 0;

                if (desktop) {
                    const all = [...desktop.querySelectorAll('[data-account-item]')];
                    const filtered = filteredItems(desktop);
                    resultCount = filtered.length;
                    const totalPages = Math.max(1, Math.ceil(filtered.length / perPage));
                    state.page = Math.min(state.page, totalPages);
                    all.forEach(item => {
                        item.hidden = true;
                        const detail = desktop.querySelector(`[data-account-detail="${item.dataset.accountKey}"]`);
                        detail?.classList.add('hidden');
                        const trigger = item.querySelector('[data-expand-account]');
                        trigger?.setAttribute('aria-expanded', 'false');
                    });
                    filtered.slice((state.page - 1) * perPage, state.page * perPage).forEach(item => {
                        const detail = desktop.querySelector(`[data-account-detail="${item.dataset.accountKey}"]`);
                        item.hidden = false;
                        item.parentElement.appendChild(item);
                        if (detail) item.parentElement.appendChild(detail);
                    });
                    renderPagination(totalPages);
                }

                if (mobile) {
                    const all = [...mobile.querySelectorAll('[data-account-item]')];
                    const filtered = filteredItems(mobile);
                    if (!resultCount) resultCount = filtered.length;
                    all.forEach(item => item.hidden = true);
                    filtered.slice(0, state.mobileLimit).forEach(item => {
                        item.hidden = false;
                        mobile.appendChild(item);
                    });
                    const loadMore = document.getElementById('loadMoreAccounts');
                    if (loadMore) loadMore.hidden = state.mobileLimit >= filtered.length;
                }

                if (summary) {
                    const desktopShown = Math.min(perPage, Math.max(0, resultCount - ((state.page - 1) * perPage)));
                    const shown = window.innerWidth < 1024 ? Math.min(state.mobileLimit, resultCount) : desktopShown;
                    summary.textContent = resultCount ? `Showing ${shown} of ${resultCount} matching accounts` : 'No matching accounts';
                }
            };

            const renderPagination = (totalPages) => {
                const pagination = document.getElementById('desktopPagination');
                if (!pagination) return;
                pagination.innerHTML = '';
                const button = (label, page, active = false, disabled = false) => {
                    const el = document.createElement('button');
                    el.type = 'button';
                    el.textContent = label;
                    el.disabled = disabled;
                    el.className = `grid h-9 min-w-9 place-items-center rounded-md border px-2 text-xs font-semibold ${active ? 'border-[#10223f] bg-[#10223f] text-white' : 'border-[#d9e1ea] bg-white text-[#4f5b6b]'} disabled:opacity-40`;
                    el.addEventListener('click', () => { state.page = page; render(); });
                    pagination.appendChild(el);
                };
                button('‹', Math.max(1, state.page - 1), false, state.page === 1);
                for (let page = 1; page <= totalPages; page++) {
                    if (totalPages > 7 && Math.abs(page - state.page) > 2 && page !== 1 && page !== totalPages) continue;
                    button(String(page), page, page === state.page);
                }
                button('›', Math.min(totalPages, state.page + 1), false, state.page === totalPages);
            };

            dashboard.querySelectorAll('[data-status-tab]').forEach(tab => tab.addEventListener('click', () => {
                state.status = tab.dataset.statusTab;
                state.page = 1;
                state.mobileLimit = 10;
                dashboard.querySelectorAll('[data-status-tab]').forEach(item => {
                    const active = item === tab;
                    item.classList.toggle('bg-[#10223f]', active);
                    item.classList.toggle('text-white', active);
                    item.classList.toggle('bg-white', !active);
                    item.classList.toggle('text-[#4f5b6b]', !active);
                });
                render();
            }));

            dashboard.querySelectorAll('[data-expand-account]').forEach(trigger => trigger.addEventListener('click', () => {
                const detail = dashboard.querySelector(`[data-account-detail="${trigger.dataset.expandAccount}"]`);
                if (!detail) return;
                const willOpen = detail.classList.contains('hidden');
                detail.classList.toggle('hidden', !willOpen);
                trigger.setAttribute('aria-expanded', String(willOpen));
                trigger.classList.toggle('rotate-180', willOpen);
            }));

            search?.addEventListener('input', () => { state.search = search.value.trim().toLowerCase(); state.page = 1; state.mobileLimit = 10; render(); });
            lender?.addEventListener('change', () => { state.lender = lender.value; if (mobileLender) mobileLender.value = lender.value; state.page = 1; render(); });
            mobileLender?.addEventListener('change', () => { state.lender = mobileLender.value; if (lender) lender.value = mobileLender.value; state.page = 1; render(); });
            sort?.addEventListener('change', () => { state.sort = sort.value; if (mobileSort) mobileSort.value = sort.value; render(); });
            mobileSort?.addEventListener('change', () => { state.sort = mobileSort.value; if (sort) sort.value = mobileSort.value; render(); });
            document.getElementById('mobileFiltersButton')?.addEventListener('click', () => document.getElementById('mobileFiltersPanel')?.classList.toggle('hidden'));
            document.getElementById('loadMoreAccounts')?.addEventListener('click', () => { state.mobileLimit += 10; render(); });
            render();
        });
    </script>
@endsection
