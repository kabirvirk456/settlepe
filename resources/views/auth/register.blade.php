@extends('layouts.auth')

@section('title', 'Register | Settle Pe')
@section('panel_width', 'w-full max-w-2xl')

@section('content')
<section class="overflow-hidden rounded-xl border border-[#dfe5ec] bg-white shadow-sm">
    <div class="bg-[#10223f] px-6 py-7 text-white md:px-8">
        <p class="text-xs font-semibold uppercase tracking-wide text-[#f4c877]">Create your account</p>
        <h1 class="mt-3 text-3xl font-semibold">Start your Settle Pe assessment</h1>
        <p class="mt-3 text-sm leading-6 text-white/70">Register with your mobile number, verify the WhatsApp OTP, and complete your financial profile.</p>
    </div>

    <div class="px-6 py-7 md:px-8">
        <form method="POST" action="{{ route('register.store') }}" class="space-y-5">
            @csrf
            <input type="hidden" name="flow" value="register">
            <div>
                <label for="register_mobile" class="block text-sm font-medium text-[#374151]">Mobile number</label>
                <div class="mt-2 flex rounded-md border border-[#cfd7e2] focus-within:border-[#10223f] focus-within:ring-2 focus-within:ring-[#10223f]/10">
                    <span class="flex items-center border-r border-[#cfd7e2] px-3 text-sm font-medium text-[#526071]">+91</span>
                    <input id="register_mobile" name="mobile" type="tel" value="{{ old('mobile', $otpMobile) }}" required autofocus inputmode="numeric" autocomplete="tel" placeholder="10-digit mobile number" class="min-w-0 flex-1 rounded-r-md border-0 px-3 py-3 text-sm outline-none">
                </div>
                @error('mobile')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <label class="flex items-start gap-3 rounded-md border border-[#dfe5ec] bg-[#f7f9fb] px-4 py-3">
                <input name="accept_terms" type="checkbox" value="1" required @checked(old('accept_terms')) class="mt-0.5 h-4 w-4 rounded border-[#9ca3af] text-[#10223f] focus:ring-[#10223f]">
                <span class="text-sm leading-6 text-[#4b5563]">I have read and accept the <a href="{{ route('terms') }}" target="_blank" class="font-semibold text-[#10223f] underline">Terms and Conditions</a>, including consent to service communications and processing of credit information for the requested services.</span>
            </label>
            @error('accept_terms')<p class="text-sm text-red-600">{{ $message }}</p>@enderror

            <button class="w-full rounded-md bg-[#10223f] px-4 py-3 text-sm font-semibold text-white">{{ $otpMobile ? 'Resend registration OTP' : 'Accept and send OTP' }}</button>
        </form>

        @if($otpMobile)
            <div class="my-7 h-px bg-[#e5eaf0]"></div>
            <form method="POST" action="{{ route('login.verify') }}" class="space-y-5">
                @csrf
                <div><p class="text-xs font-semibold uppercase tracking-wide text-[#42a371]">OTP sent</p><p class="mt-1 text-sm text-[#526071]">Enter the OTP sent to +91 {{ $otpMobile }}</p></div>
                <div><label for="register_otp" class="block text-sm font-medium text-[#374151]">One-time password</label><input id="register_otp" name="otp" type="text" required inputmode="numeric" maxlength="6" autocomplete="one-time-code" placeholder="6-digit OTP" class="mt-2 w-full rounded-md border border-[#cfd7e2] px-3 py-3 text-sm tracking-[0.35em] outline-none">@error('otp')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror</div>
                <button class="w-full rounded-md bg-[#f4c877] px-4 py-3 text-sm font-semibold text-[#10223f]">Verify and create account</button>
            </form>
        @endif

        <p class="mt-7 border-t border-[#e5eaf0] pt-5 text-center text-sm text-[#6b7280]">Already registered? <a href="{{ route('login') }}" class="font-semibold text-[#10223f] underline">Log in</a></p>
    </div>
</section>
@endsection
