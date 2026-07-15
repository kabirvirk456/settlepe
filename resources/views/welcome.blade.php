@php($registerUrl = route('register'))

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Settle Pe</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: #f7f9fc;
        }

        html {
            scroll-behavior: smooth;
        }

        .hero-media {
            background-image:
                linear-gradient(90deg, rgba(5, 20, 41, 0.94) 0%, rgba(5, 20, 41, 0.82) 42%, rgba(5, 20, 41, 0.38) 72%, rgba(5, 20, 41, 0.62) 100%),
                url('{{ asset('images/hero.jpg') }}');
            background-position: center;
            background-size: cover;
        }

        .settle-range {
            -webkit-appearance: none;
            appearance: none;
            height: 6px;
            border-radius: 999px;
            background: linear-gradient(to right, #0b6846 0%, #0b6846 25%, #d8e1ec 25%, #d8e1ec 100%);
            outline: none;
        }

        .settle-range::-webkit-slider-thumb {
            -webkit-appearance: none;
            appearance: none;
            width: 18px;
            height: 18px;
            background: #f4c877;
            border: 2px solid #0b6846;
            border-radius: 999px;
            cursor: pointer;
        }

        .settle-range::-moz-range-thumb {
            width: 18px;
            height: 18px;
            background: #f4c877;
            border: 2px solid #0b6846;
            border-radius: 999px;
            cursor: pointer;
        }

        [x-cloak] {
            display: none !important;
        }
    </style>
</head>
<body class="text-[#111827]">
    <header x-data="{ open: false }" class="fixed inset-x-0 top-0 z-50 border-b border-[#e4eaf2] bg-white/95 backdrop-blur">
        <div class="mx-auto flex h-[68px] w-full max-w-7xl items-center justify-between px-4 md:px-6">
            <a href="#home" class="text-lg font-extrabold tracking-wide text-[#071b34]">
                SETTLE<span class="text-[#0b6846]">PE</span>
            </a>

            <nav class="hidden items-center gap-6 text-[13px] font-medium text-[#526071] md:flex">
                <a href="#home" class="transition hover:text-[#071b34]">Home</a>
                <a href="#services" class="transition hover:text-[#071b34]">Services</a>
                <a href="#how-it-works" class="transition hover:text-[#071b34]">How It Works</a>
                <a href="#faq" class="transition hover:text-[#071b34]">FAQs</a>
                <a href="#contact" class="transition hover:text-[#071b34]">Contact</a>
                @auth
                    <a href="{{ route('dashboard') }}" class="transition hover:text-[#071b34]">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="transition hover:text-[#071b34]">Login</a>
                    <a href="{{ route('register') }}" class="transition hover:text-[#071b34]">Register</a>
                @endauth
            </nav>

            <div class="hidden items-center gap-3 md:flex">
                @guest
                    <a href="{{ route('login') }}" class="rounded-md border border-[#ccd6e2] px-4 py-2 text-[13px] font-semibold text-[#071b34] transition hover:border-[#071b34]">Login</a>
                @else
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="rounded-md border border-[#ccd6e2] px-4 py-2 text-[13px] font-semibold text-[#071b34] transition hover:border-[#071b34]">Logout</button>
                    </form>
                @endguest
                <a href="{{ $registerUrl }}" class="rounded-md bg-[#071b34] px-4 py-2 text-[13px] font-semibold text-white transition hover:bg-[#0e2d54]">Register</a>
            </div>

            <button type="button" @click="open = !open" class="grid h-10 w-10 place-items-center rounded-md border border-[#d8e1ec] text-2xl text-[#071b34] md:hidden" aria-label="Toggle navigation">
                ☰
            </button>
        </div>

        <div x-cloak x-show="open" x-transition class="border-t border-[#e4eaf2] bg-white px-4 py-5 text-[14px] font-medium text-[#526071] md:hidden">
            <div class="mx-auto flex max-w-7xl flex-col gap-4">
                <a href="#home" @click="open = false">Home</a>
                <a href="#services" @click="open = false">Services</a>
                <a href="#how-it-works" @click="open = false">How It Works</a>
                <a href="#faq" @click="open = false">FAQs</a>
                <a href="#contact" @click="open = false">Contact</a>
                @auth
                    <a href="{{ route('dashboard') }}">Dashboard</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full text-left">Logout</button>
                    </form>
                @else
                    <a href="{{ route('login') }}">Login</a>
                    <a href="{{ route('register') }}">Register</a>
                @endauth
                <a href="{{ $registerUrl }}" class="rounded-md bg-[#071b34] px-4 py-3 text-center font-semibold text-white">Start Settlement</a>
            </div>
        </div>
    </header>

    <main>
        <section id="home" class="hero-media pt-[68px] text-white">
            <div class="mx-auto flex min-h-[calc(100vh-68px)] w-full max-w-7xl flex-col justify-center px-4 py-10 md:px-6 md:py-14">
                <div class="grid gap-8 lg:grid-cols-[1fr_360px] lg:items-end">
                    <div class="max-w-[720px]">
                        <div class="mb-5 inline-flex w-fit rounded-md border border-white/20 bg-white/10 px-4 py-2 text-[12px] font-semibold text-[#f4c877]">
                            Structured Loan Settlement Assistance
                        </div>

                        <h1 class="text-[34px] font-extrabold leading-[42px] md:text-[56px] md:leading-[64px]">
                            Struggling With Loan Repayments?
                            <span class="block">We Help You Settle It</span>
                            <span class="text-[#f4c877]">Legally & With Support.</span>
                        </h1>

                        <p class="mt-5 max-w-[560px] text-[15px] leading-7 text-slate-200 md:text-[16px]">
                            Understand your debts and get structured negotiation, case handling, and borrower-rights support. Final settlement terms are decided by each lender.
                        </p>

                        <div class="mt-6 flex flex-wrap gap-3 text-[12px] font-medium text-white">
                            <span class="rounded-md bg-white/12 px-3 py-2 ring-1 ring-white/10">Secure Process</span>
                            <span class="rounded-md bg-white/12 px-3 py-2 ring-1 ring-white/10">Debt Resolution Experts</span>
                            <span class="rounded-md bg-white/12 px-3 py-2 ring-1 ring-white/10">Confidential Support</span>
                        </div>

                        <div class="mt-7 flex flex-col gap-3 sm:flex-row">
                            <a href="{{ $registerUrl }}" class="rounded-md bg-[#f4c877] px-6 py-3 text-center text-[14px] font-bold text-[#071b34] transition hover:bg-[#e7b95f]">
                                Start Settlement
                            </a>
                            <a href="{{ route('login') }}" class="rounded-md border border-white/25 bg-white/10 px-6 py-3 text-center text-[14px] font-bold text-white transition hover:bg-white/15">
                                Existing customer login
                            </a>
                        </div>
                    </div>

                    <div class="grid gap-3">
                        <div class="rounded-lg border border-white/20 bg-white/95 p-4 text-[#111827] shadow-xl">
                            <div class="flex items-start gap-3">
                                <span class="grid h-9 w-9 shrink-0 place-items-center rounded-md bg-[#eefaf2]">
                                    <svg viewBox="0 0 24 24" class="h-5 w-5 text-[#0b6846]" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <path d="M12 3l7 3v5c0 5-3.4 8.6-7 10-3.6-1.4-7-5-7-10V6l7-3z" />
                                        <path d="M9 12l2 2 4-5" />
                                    </svg>
                                </span>
                                <p class="text-[12px] leading-5 text-[#1f2937]">
                                    Settle Pe provides structured debt-resolution support with assistance from
                                    <span class="font-semibold text-[#0b6846]">BM Legal Firm.</span>
                                </p>
                            </div>
                        </div>

                        <div class="grid gap-2 text-[13px] text-[#111827] sm:grid-cols-3 lg:grid-cols-1">
                            <div class="rounded-md bg-white px-4 py-3 shadow-lg">Harassment Calls?</div>
                            <div class="rounded-md bg-white px-4 py-3 shadow-lg">High Interest?</div>
                            <div class="rounded-md bg-white px-4 py-3 shadow-lg">Legal Notices?</div>
                        </div>

                        <div class="rounded-lg bg-white px-5 py-4 text-[14px] font-semibold text-[#111827] shadow-xl">
                            We Handle Everything.
                            <span class="block font-medium text-[#64748b]">Focus on Your Future.</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="calculator" class="bg-[#f7f9fc] py-12 md:py-16">
            <div class="mx-auto w-full max-w-7xl px-4 md:px-6">
                <div class="grid overflow-hidden rounded-lg border border-[#dce5ef] bg-white shadow-sm lg:grid-cols-[0.92fr_1.08fr]">
                    <div class="bg-[#071b34] p-6 text-white md:p-8">
                        <h2 class="text-[24px] font-bold">Start With a Clear Debt Overview</h2>
                        <p class="mt-3 text-[13px] leading-6 text-slate-300">Add your balances to understand the total amount that needs review.</p>

                        <div class="mt-8 rounded-lg border border-white/15 bg-white/10 p-5">
                            <p class="text-[12px] font-semibold uppercase tracking-wide text-slate-300">What happens next</p>
                            <ol class="mt-4 space-y-3 text-[13px] leading-6 text-slate-200">
                                <li>1. Verify your identity and fetch your CRIF credit report.</li>
                                <li>2. Review lender-wise balances with a Settle Pe expert.</li>
                                <li>3. Decide whether settlement assistance suits your circumstances.</li>
                            </ol>
                            <p class="mt-4 text-[12px] leading-5 text-slate-400">No reduction or settlement is guaranteed. Any offer requires lender approval.</p>

                            <a href="{{ $registerUrl }}" class="mt-5 block rounded-md bg-[#f4c877] px-6 py-3 text-center text-[14px] font-bold text-[#071b34] transition hover:bg-[#e7b95f]">
                                Start Settlement
                            </a>
                        </div>
                    </div>

                    <div class="p-5 md:p-8">
                        <div class="mb-6">
                            <p class="text-[12px] font-semibold uppercase tracking-wide text-[#0b6846]">Enter Your Debt Details</p>
                            <h3 class="mt-2 text-[24px] font-extrabold text-[#071b34]">Calculate your total debt</h3>
                            <p class="mt-2 text-[13px] leading-6 text-[#64748b]">Add your credit card and personal loan balances. This is a debt total, not a settlement quote.</p>
                        </div>

                        <div class="grid gap-5">
                            <div class="rounded-lg border border-[#dce5ef] bg-[#fbfcfe] p-5">
                                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                    <label for="creditCardAmount" class="text-[14px] font-bold text-[#334155]">Credit Card Debt Amount</label>
                                    <input id="creditCardAmount" type="text" inputmode="numeric" value="300000" class="h-11 w-full rounded-md border border-[#ccd6e2] bg-white px-3 text-[15px] font-semibold outline-none focus:border-[#0b6846] focus:ring-2 focus:ring-[#0b6846]/10 sm:w-[180px]">
                                </div>
                                <input id="creditCardSlider" type="range" min="0" max="5000000" step="10000" value="300000" class="settle-range mt-5 w-full">
                                <div class="mt-2 flex justify-between text-[11px] text-[#94a3b8]">
                                    <span>Rs. 0</span>
                                    <span>Rs. 50,00,000</span>
                                </div>
                            </div>

                            <div class="rounded-lg border border-[#dce5ef] bg-[#fbfcfe] p-5">
                                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                    <label for="personalLoanAmount" class="text-[14px] font-bold text-[#334155]">Personal Loan Debt Amount</label>
                                    <input id="personalLoanAmount" type="text" inputmode="numeric" value="200000" class="h-11 w-full rounded-md border border-[#ccd6e2] bg-white px-3 text-[15px] font-semibold outline-none focus:border-[#0b6846] focus:ring-2 focus:ring-[#0b6846]/10 sm:w-[180px]">
                                </div>
                                <input id="personalLoanSlider" type="range" min="0" max="5000000" step="10000" value="200000" class="settle-range mt-5 w-full">
                                <div class="mt-2 flex justify-between text-[11px] text-[#94a3b8]">
                                    <span>Rs. 0</span>
                                    <span>Rs. 50,00,000</span>
                                </div>
                            </div>

                            <div class="flex flex-col gap-4 rounded-lg bg-[#071b34] p-5 text-white sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <p class="text-[12px] font-semibold uppercase tracking-wide text-slate-300">Total Debt Entered</p>
                                    <p id="totalDebtAmount" class="mt-1 text-[26px] font-extrabold">Rs. 5,00,000</p>
                                </div>
                                <a href="{{ $registerUrl }}" class="rounded-md bg-[#f4c877] px-6 py-3 text-center text-[14px] font-bold text-[#071b34] transition hover:bg-[#e7b95f]">
                                    Start Settlement
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="bg-white py-12 md:py-16">
            <div class="mx-auto max-w-6xl px-4 md:px-6">
                <div class="mb-9 max-w-[620px]">
                    <h2 class="text-[28px] font-extrabold text-[#111827]">The Problem Borrowers Face</h2>
                    <p class="mt-2 text-[13px] text-[#64748b]">You are not alone. We understand what you are going through.</p>
                </div>

                <div class="grid gap-5 md:grid-cols-2">
                    <article class="grid overflow-hidden rounded-lg border border-[#f3caca] bg-[#fff8f8] md:grid-cols-[1fr_190px]">
                        <div class="p-5 md:p-6">
                            <div class="mb-4 flex items-center justify-between">
                                <h3 class="text-[15px] font-bold text-[#dc2626]">What You Face</h3>
                                <span class="grid h-8 w-8 place-items-center rounded-md bg-[#dc2626] text-white">×</span>
                            </div>
                            <div class="space-y-3 text-[13px] leading-6 text-[#374151]">
                                <p>High interest rates and increasing penalties</p>
                                <p>Harassment calls and threatening recovery tactics</p>
                                <p>Legal notices and lack of legal awareness</p>
                                <p>No proper guidance or structured negotiation channel</p>
                            </div>
                        </div>
                        <div class="flex items-end justify-center bg-[#fff1f1] px-4 pt-4">
                            <img src="{{ asset('images/stress-man.png') }}" alt="Borrower under repayment stress" class="h-[190px] object-contain md:h-[230px]">
                        </div>
                    </article>

                    <article class="grid overflow-hidden rounded-lg border border-[#bde5c8] bg-[#f6fff9] md:grid-cols-[1fr_190px]">
                        <div class="p-5 md:p-6">
                            <div class="mb-4 flex items-center justify-between">
                                <h3 class="text-[15px] font-bold text-[#15803d]">Our Solution</h3>
                                <span class="grid h-8 w-8 place-items-center rounded-md bg-[#16a34a] text-white">✓</span>
                            </div>
                            <div class="space-y-3 text-[13px] leading-6 text-[#374151]">
                                <p>We negotiate legally to reduce your outstanding</p>
                                <p>We handle harassment and legal notices</p>
                                <p>We protect your rights and provide legal support</p>
                                <p>We provide a structured and transparent settlement process</p>
                            </div>
                        </div>
                        <div class="flex items-end justify-center bg-[#ecfff2] px-4 pt-4">
                            <img src="{{ asset('images/happy-man.png') }}" alt="Borrower after settlement support" class="h-[190px] object-contain md:h-[230px]">
                        </div>
                    </article>
                </div>

                <div class="mt-8 flex flex-col items-start justify-between gap-4 border-l-4 border-[#0b6846] bg-[#f7f9fc] px-5 py-5 md:flex-row md:items-center md:px-6">
                    <p class="text-[14px] font-medium text-[#334155]">Still facing these issues? Let our experts handle it for you.</p>
                    <a href="{{ $registerUrl }}" class="rounded-md bg-[#071b34] px-5 py-3 text-[14px] font-bold text-white transition hover:bg-[#0e2d54]">Talk to Our Legal Expert</a>
                </div>
            </div>
        </section>

        <section id="services" class="bg-[#f7f9fc] py-12 md:py-16">
            <div class="mx-auto max-w-6xl px-4 md:px-6">
                <div class="mb-9 text-center">
                    <h2 class="text-[28px] font-extrabold text-[#111827]">Our Core Services</h2>
                    <p class="mt-2 text-[13px] text-[#64748b]">Comprehensive support for loan settlement, legal response, and case handling.</p>
                </div>

                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <article class="rounded-lg border border-[#e0e7f0] bg-white p-5 shadow-sm transition hover:-translate-y-1 hover:shadow-md">
                        <div class="mb-4 grid h-11 w-11 place-items-center rounded-md bg-[#e8f8ee] text-[18px] font-extrabold text-[#15803d]">₹</div>
                        <h3 class="text-[15px] font-bold">Loan Settlement</h3>
                        <p class="mt-2 text-[13px] leading-6 text-[#64748b]">Negotiation with banks and NBFCs to reduce outstanding loan burden.</p>
                        <a href="{{ $registerUrl }}" class="mt-4 inline-block rounded-md border border-[#dbe3ee] px-3 py-2 text-[12px] font-bold text-[#071b34] hover:bg-[#f8fafc]">Learn More</a>
                    </article>

                    <article class="rounded-lg border border-[#e0e7f0] bg-white p-5 shadow-sm transition hover:-translate-y-1 hover:shadow-md">
                        <div class="mb-4 grid h-11 w-11 place-items-center rounded-md bg-[#eef2ff] text-[18px] font-extrabold text-[#4338ca]">§</div>
                        <h3 class="text-[15px] font-bold">Legal Assistance</h3>
                        <p class="mt-2 text-[13px] leading-6 text-[#64748b]">Support for recovery pressure, notices, and borrower-rights guidance.</p>
                        <a href="{{ $registerUrl }}" class="mt-4 inline-block rounded-md border border-[#dbe3ee] px-3 py-2 text-[12px] font-bold text-[#071b34] hover:bg-[#f8fafc]">Learn More</a>
                    </article>

                    <article class="rounded-lg border border-[#e0e7f0] bg-white p-5 shadow-sm transition hover:-translate-y-1 hover:shadow-md">
                        <div class="mb-4 grid h-11 w-11 place-items-center rounded-md bg-[#fff3e8] text-[18px] font-extrabold text-[#c2410c]">%</div>
                        <h3 class="text-[15px] font-bold">Debt Management</h3>
                        <p class="mt-2 text-[13px] leading-6 text-[#64748b]">Financial assessment, debt restructuring, and credit recovery planning.</p>
                        <a href="{{ $registerUrl }}" class="mt-4 inline-block rounded-md border border-[#dbe3ee] px-3 py-2 text-[12px] font-bold text-[#071b34] hover:bg-[#f8fafc]">Learn More</a>
                    </article>

                    <article class="rounded-lg border border-[#e0e7f0] bg-white p-5 shadow-sm transition hover:-translate-y-1 hover:shadow-md">
                        <div class="mb-4 grid h-11 w-11 place-items-center rounded-md bg-[#eef7ff] text-[18px] font-extrabold text-[#0369a1]">✓</div>
                        <h3 class="text-[15px] font-bold">Case Handling</h3>
                        <p class="mt-2 text-[13px] leading-6 text-[#64748b]">Dedicated case manager, document follow-ups, and settlement tracking.</p>
                        <a href="{{ $registerUrl }}" class="mt-4 inline-block rounded-md border border-[#dbe3ee] px-3 py-2 text-[12px] font-bold text-[#071b34] hover:bg-[#f8fafc]">Learn More</a>
                    </article>
                </div>

                <section id="how-it-works" class="pt-14 md:pt-16">
                    <div class="mb-9 text-center">
                        <h2 class="text-[28px] font-extrabold text-[#111827]">How It Works</h2>
                        <p class="mt-2 text-[13px] text-[#64748b]">A clear 5-step process to help you get debt relief legally.</p>
                    </div>

                    <div class="grid gap-3 md:grid-cols-5">
                        @foreach ([
                            ['Case Evaluation', 'Analyze loan details and repayment capacity'],
                            ['Strategy Planning', 'Build a settlement strategy for your case'],
                            ['Negotiation', 'Work toward the best possible outcome'],
                            ['Legal Protection', 'Handle notices and recovery pressure'],
                            ['Closure', 'Guide closure and financial recovery'],
                        ] as $index => [$title, $copy])
                            <article class="relative rounded-lg border border-[#dbe3ee] bg-white p-4 text-left shadow-sm">
                                <div class="mb-3 grid h-9 w-9 place-items-center rounded-md bg-[#071b34] text-[12px] font-bold text-white">{{ $index + 1 }}</div>
                                <h3 class="text-[13px] font-bold">{{ $title }}</h3>
                                <p class="mt-2 text-[12px] leading-5 text-[#64748b]">{{ $copy }}</p>
                            </article>
                        @endforeach
                    </div>

                    <div class="mt-8 text-center">
                        <a href="{{ $registerUrl }}" class="inline-block rounded-md bg-[#071b34] px-6 py-3 text-[14px] font-bold text-white transition hover:bg-[#0e2d54]">Start Your Case Today</a>
                    </div>
                </section>
            </div>
        </section>

        <section class="bg-white py-12 md:py-16">
            <div class="mx-auto max-w-6xl px-4 md:px-6">
                <div class="text-center"><h2 class="text-[28px] font-extrabold">What You Can Expect</h2><p class="mt-2 text-[13px] text-[#64748b]">Clear information at each stage of your case.</p></div>
                <div class="mt-8 grid gap-4 md:grid-cols-3">
                    <article class="rounded-lg border border-[#dbe3ee] bg-[#f7f9fc] p-5"><h3 class="font-bold">Transparent review</h3><p class="mt-2 text-[13px] leading-6 text-[#64748b]">We review your verified report and explain the accounts that need attention.</p></article>
                    <article class="rounded-lg border border-[#dbe3ee] bg-[#f7f9fc] p-5"><h3 class="font-bold">Documented progress</h3><p class="mt-2 text-[13px] leading-6 text-[#64748b]">Track documents, lender communications, recorded offers, and next actions.</p></article>
                    <article class="rounded-lg border border-[#dbe3ee] bg-[#f7f9fc] p-5"><h3 class="font-bold">No outcome promises</h3><p class="mt-2 text-[13px] leading-6 text-[#64748b]">We provide assistance, while each lender independently decides whether to make or accept an offer.</p></article>
                </div>
            </div>
        </section>

        <section class="bg-[#f7f9fc] py-12">
            <div class="mx-auto max-w-6xl px-4 md:px-6">
                <div class="flex flex-col gap-6 overflow-hidden rounded-lg bg-[#071b34] px-5 py-7 text-white md:flex-row md:items-center md:justify-between md:px-8">
                    <div>
                        <h2 class="text-[24px] font-extrabold">Don’t Wait. Settle Your Loan Today!</h2>
                        <p class="mt-2 max-w-[620px] text-[13px] leading-6 text-slate-300">Every day of delay increases your burden. Let our experts help you get a fresh start.</p>
                    </div>
                    <a href="{{ $registerUrl }}" class="rounded-md bg-[#f4c877] px-6 py-3 text-center text-[14px] font-bold text-[#071b34] transition hover:bg-[#e7b95f]">Start Settlement</a>
                </div>
            </div>
        </section>

        <section id="faq" class="bg-[#f7f9fc] pb-12 md:pb-16">
            <div class="mx-auto max-w-4xl px-4 md:px-6">
                <div class="mb-8 text-center">
                    <h2 class="text-[28px] font-extrabold text-[#111827]">Frequently Asked Questions</h2>
                    <p class="mt-2 text-[13px] text-[#64748b]">Everything you need to know about loan settlement.</p>
                </div>

                <div class="space-y-3">
                    @foreach ([
                        ['Is loan settlement legal in India?', 'Yes, loan settlement is a legally accepted process where lenders agree to accept a reduced amount as full payment under financial hardship cases.'],
                        ['Will settlement affect my credit score?', 'A settled account can negatively affect your credit history. The effect depends on the lender’s reporting and your wider credit profile.'],
                        ['How much loan reduction can I expect?', 'There is no fixed reduction. Any offer depends on your circumstances, account status, lender policy, and lender approval.'],
                        ['How long does the process take?', 'Timelines vary by lender response, documentation, account status, and case complexity. Your case tracker will show recorded progress.'],
                    ] as [$question, $answer])
                        <div x-data="{ open: false }" class="rounded-lg border border-[#dbe3ee] bg-white shadow-sm">
                            <button type="button" @click="open = !open" class="flex w-full items-center justify-between gap-4 px-5 py-4 text-left text-[14px] font-bold text-[#111827]">
                                <span>{{ $question }}</span>
                                <span x-text="open ? '-' : '+'" class="text-lg"></span>
                            </button>
                            <div x-cloak x-show="open" x-transition class="px-5 pb-4 text-[13px] leading-6 text-[#64748b]">
                                {{ $answer }}
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-8 text-center">
                    <p class="mb-3 text-[13px] text-[#64748b]">Still have questions?</p>
                    <a href="{{ $registerUrl }}" class="inline-block rounded-md bg-[#071b34] px-6 py-3 text-[14px] font-bold text-white transition hover:bg-[#0e2d54]">Talk to Our Legal Expert</a>
                </div>
            </div>
        </section>
    </main>

    <footer id="contact" class="border-t border-[#dbe3ee] bg-white py-10">
        <div class="mx-auto grid max-w-6xl gap-8 px-4 md:grid-cols-4 md:px-6">
            <div>
                <h2 class="mb-3 text-[16px] font-extrabold">SETTLE PE</h2>
                <p class="text-[13px] leading-6 text-[#64748b]">Settle Pe is a legal-financial assistance platform committed to helping borrowers achieve debt relief with dignity.</p>
            </div>

            <div>
                <h3 class="mb-3 text-[13px] font-bold">Quick Links</h3>
                <ul class="space-y-2 text-[13px] text-[#64748b]">
                    <li><a href="#home" class="hover:text-[#111827]">Home</a></li>
                    <li><a href="#services" class="hover:text-[#111827]">Services</a></li>
                    <li><a href="#how-it-works" class="hover:text-[#111827]">How It Works</a></li>
                    <li><a href="#faq" class="hover:text-[#111827]">FAQs</a></li>
                    @auth
                        <li><a href="{{ route('dashboard') }}" class="hover:text-[#111827]">Dashboard</a></li>
                    @else
                        <li><a href="{{ route('login') }}" class="hover:text-[#111827]">Login</a></li>
                        <li><a href="{{ route('register') }}" class="hover:text-[#111827]">Register</a></li>
                    @endauth
                </ul>
            </div>

            <div>
                <h3 class="mb-3 text-[13px] font-bold">Our Services</h3>
                <ul class="space-y-2 text-[13px] text-[#64748b]">
                    <li>Loan Settlement</li>
                    <li>Legal Assistance</li>
                    <li>Debt Management Advisory</li>
                    <li>Case Handling System</li>
                </ul>
            </div>

            <div>
                <h3 class="mb-3 text-[13px] font-bold">Get in Touch</h3>
                <ul class="space-y-2 text-[13px] text-[#64748b]">
                    <li>+91 9137696147</li>
                    <li><a href="mailto:support@settlepe.in" class="hover:text-[#111827]">support@settlepe.in</a></li>
                    <li><a href="{{ route('terms') }}" class="hover:underline">Terms and Conditions</a></li>
                    <li>Mumbai</li>
                    <li>Mon - Sat 9:00 AM - 7:00 PM</li>
                </ul>
            </div>
        </div>

        <div class="mx-auto mt-8 max-w-6xl px-4 text-center text-[12px] text-[#94a3b8] md:px-6">
            © {{ date('Y') }} Settle Pe. All Rights Reserved.
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const creditCardInput = document.getElementById('creditCardAmount');
            const creditCardSlider = document.getElementById('creditCardSlider');
            const personalLoanInput = document.getElementById('personalLoanAmount');
            const personalLoanSlider = document.getElementById('personalLoanSlider');
            const totalDebtAmount = document.getElementById('totalDebtAmount');
            if (! creditCardInput || ! creditCardSlider || ! personalLoanInput || ! personalLoanSlider || ! totalDebtAmount) {
                return;
            }

            function normalizeAmount(value) {
                const numeric = parseInt(String(value).replace(/[^0-9]/g, ''), 10);
                return Number.isFinite(numeric) ? numeric : 0;
            }

            function formatINR(amount) {
                return 'Rs. ' + amount.toLocaleString('en-IN');
            }

            function updateSliderTrack(slider) {
                const min = Number(slider.min);
                const max = Number(slider.max);
                const value = Number(slider.value);
                const percent = ((value - min) / (max - min)) * 100;

                slider.style.background = `linear-gradient(to right, #0b6846 0%, #0b6846 ${percent}%, #d8e1ec ${percent}%, #d8e1ec 100%)`;
            }

            function calculate() {
                const amount = normalizeAmount(creditCardInput.value) + normalizeAmount(personalLoanInput.value);
                totalDebtAmount.innerText = formatINR(amount);
                updateSliderTrack(creditCardSlider);
                updateSliderTrack(personalLoanSlider);
            }

            function bindAmountControl(input, slider) {
                slider.addEventListener('input', function () {
                    input.value = slider.value;
                    calculate();
                });

                input.addEventListener('input', function () {
                    const value = normalizeAmount(input.value);
                    slider.value = Math.min(Math.max(value, Number(slider.min)), Number(slider.max));
                    calculate();
                });

                input.addEventListener('blur', function () {
                    input.value = normalizeAmount(input.value).toLocaleString('en-IN');
                });
            }

            bindAmountControl(creditCardInput, creditCardSlider);
            bindAmountControl(personalLoanInput, personalLoanSlider);

            calculate();
        });
    </script>
</body>
</html>
