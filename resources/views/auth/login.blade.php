@extends('layouts.auth')

@section('title', 'Credit Report Login | Settle Pe')
@section('panel_width', 'w-full max-w-5xl')

@section('content')
    <section class="grid overflow-hidden rounded-lg border border-[#dfe5ec] bg-white shadow-sm lg:grid-cols-[0.92fr_1.08fr]">
        <div class="bg-[#10223f] px-6 py-8 text-white md:px-8 md:py-10">
            <p class="text-xs font-semibold uppercase tracking-wide text-[#f4c877]">Credit report assistance</p>
            <h1 class="mt-3 text-3xl font-semibold leading-tight md:text-4xl">Check your loan report with mobile OTP.</h1>
            <p class="mt-4 max-w-sm text-sm leading-6 text-[#d8e2ee]">
                Review your dues, DPD, and lender-wise balances before speaking with an advocate.
            </p>

            <div class="mt-8 space-y-5 text-sm text-[#edf4ff]">
                <div class="flex items-start gap-3">
                    <span class="mt-1 h-2.5 w-2.5 rounded-full bg-[#f4c877]"></span>
                    <span>Verify your mobile number with OTP.</span>
                </div>
                <div class="flex items-start gap-3">
                    <span class="mt-1 h-2.5 w-2.5 rounded-full bg-[#42b883]"></span>
                    <span>Submit name, age, income, gender, and PAN details.</span>
                </div>
                <div class="flex items-start gap-3">
                    <span class="mt-1 h-2.5 w-2.5 rounded-full bg-[#8bd3ff]"></span>
                    <span>View your CRIF credit report with consultation support.</span>
                </div>
            </div>

            <div class="mt-10 border-t border-white/15 pt-6">
                <p class="text-xs uppercase tracking-wide text-[#aab9cc]">Secure access</p>
                <p class="mt-2 text-sm leading-6 text-[#d8e2ee]">Your mobile number helps us keep your report access protected.</p>
            </div>
        </div>

        <div class="px-6 py-7 md:px-8 md:py-9">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-[#b7862d]">Secure login</p>
                <h2 class="mt-2 text-2xl font-semibold text-[#111827]">Welcome back</h2>
                <p class="mt-2 text-sm text-[#6b7280]">Log in to your existing Settle Pe account.</p>
            </div>

            <form method="POST" action="{{ route('login.otp') }}" class="mt-6 space-y-5">
                @csrf
                <input type="hidden" name="flow" value="login">

                <div>
                    <label for="mobile" class="block text-sm font-medium text-[#374151]">Mobile number</label>
                    <div class="mt-2 flex rounded-md border border-[#cfd7e2] focus-within:border-[#10223f] focus-within:ring-2 focus-within:ring-[#10223f]/10">
                        <span class="flex items-center border-r border-[#cfd7e2] px-3 text-sm font-medium text-[#526071]">+91</span>
                        <input
                            id="mobile"
                            name="mobile"
                            type="tel"
                            value="{{ old('mobile', $otpMobile) }}"
                            required
                            autofocus
                            inputmode="numeric"
                            autocomplete="tel"
                            placeholder="10-digit mobile number"
                            class="min-w-0 flex-1 rounded-r-md border-0 px-3 py-3 text-sm outline-none"
                        >
                    </div>
                    @error('mobile')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" class="w-full rounded-md bg-[#10223f] px-4 py-3 text-sm font-semibold text-white transition hover:bg-[#18365f]">
                    {{ $otpMobile ? 'Resend OTP' : 'Send OTP' }}
                </button>
            </form>

            @if ($otpMobile)
                <div class="my-7 h-px bg-[#e5eaf0]"></div>

                <form method="POST" action="{{ route('login.verify') }}" class="space-y-5">
                    @csrf

                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-[#42a371]">OTP sent</p>
                            <p class="mt-1 text-sm text-[#526071]">Enter the OTP sent to +91 {{ $otpMobile }}</p>
                        </div>
                    </div>

                    <div>
                        <label for="otp" class="block text-sm font-medium text-[#374151]">One-time password</label>
                        <input
                            id="otp"
                            name="otp"
                            type="text"
                            required
                            inputmode="numeric"
                            maxlength="6"
                            autocomplete="one-time-code"
                            placeholder="6-digit OTP"
                            class="mt-2 w-full rounded-md border border-[#cfd7e2] px-3 py-3 text-sm tracking-[0.35em] outline-none transition focus:border-[#10223f] focus:ring-2 focus:ring-[#10223f]/10"
                        >
                        @error('otp')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit" class="w-full rounded-md bg-[#f4c877] px-4 py-3 text-sm font-semibold text-[#10223f] transition hover:bg-[#e8bb62]">
                        Verify and continue
                    </button>
                </form>
            @endif

            <p class="mt-7 border-t border-[#e5eaf0] pt-5 text-center text-sm text-[#6b7280]">
                New to Settle Pe?
                <a href="{{ route('register') }}" class="font-semibold text-[#10223f] underline">Create an account</a>
            </p>
        </div>
    </section>
@endsection
