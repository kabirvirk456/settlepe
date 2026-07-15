@extends('layouts.auth')

@section('title', 'Fetch CRIF Report | Settle Pe')
@section('panel_width', 'w-full max-w-5xl')

@section('content')
    <section class="overflow-hidden rounded-lg border border-[#dfe5ec] bg-white shadow-sm">
        <div class="grid md:grid-cols-[0.7fr_1.3fr]">
            <div class="bg-[#f7f3e8] px-6 py-7 md:px-8 md:py-9">
                <p class="text-xs font-semibold uppercase tracking-wide text-[#9a6b18]">Applicant profile</p>
                <h1 class="mt-3 text-3xl font-semibold leading-tight text-[#111827]">Fetch your CRIF report.</h1>
                <p class="mt-4 text-sm leading-6 text-[#5f6b7a]">
                    Mobile verified for +91 {{ auth()->user()->mobile }}.
                </p>

                <div class="mt-8 space-y-4 text-sm text-[#374151]">
                    <div class="flex items-center justify-between border-b border-[#e2d8bd] pb-3">
                        <span>Report type</span>
                        <span class="font-semibold text-[#111827]">CRIF credit report</span>
                    </div>
                    <div class="flex items-center justify-between border-b border-[#e2d8bd] pb-3">
                        <span>Mobile verification</span>
                        <span class="font-semibold text-[#0d7a51]">OTP passed</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span>Current step</span>
                        <span class="font-semibold text-[#111827]">{{ $pendingReport ? 'Bureau authentication' : 'Personal details' }}</span>
                    </div>
                </div>
            </div>

            <div class="px-6 py-7 md:px-8 md:py-9">
                @if ($errors->has('crif'))
                    <div class="mb-5 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                        {{ $errors->first('crif') }}
                    </div>
                @endif

                @if ($pendingReport)
                    <form method="POST" action="{{ route('cibil.profile.authenticate') }}" class="space-y-5">
                        @csrf
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-[#6b7280]">Final verification</p>
                            <h2 class="mt-2 text-2xl font-semibold text-[#111827]">Answer the CRIF authentication question</h2>
                            <p class="mt-2 text-sm leading-6 text-[#5f6b7a]">The answer is sent securely to CRIF and is not stored by Settle Pe.</p>
                        </div>

                        @if ($pendingReport->authentication_prompt)
                            <div class="rounded-md border border-[#dfe5ec] bg-[#f7f9fb] p-4 text-sm font-medium text-[#111827]">
                                {{ $pendingReport->authentication_prompt }}
                            </div>
                        @endif

                        <div>
                            <label for="auth_answers" class="block text-sm font-medium text-[#374151]">Authentication answer</label>
                            <input id="auth_answers" name="auth_answers" type="text" value="{{ old('auth_answers') }}" required autofocus autocomplete="off" class="mt-2 w-full rounded-md border border-[#cfd7e2] px-3 py-3 text-sm outline-none transition focus:border-[#10223f] focus:ring-2 focus:ring-[#10223f]/10">
                            @error('auth_answers')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>

                        <button type="submit" class="w-full rounded-md bg-[#10223f] px-4 py-3 text-sm font-semibold text-white transition hover:bg-[#18365f]">Verify and fetch CRIF report</button>
                    </form>
                @else
                    <form method="POST" action="{{ route('cibil.profile.store') }}" class="space-y-5">
                        @csrf
                        <div class="grid gap-5 sm:grid-cols-2">
                            @foreach ([
                                ['first_name', 'First name', true],
                                ['middle_name', 'Middle name', false],
                                ['last_name', 'Last name', true],
                            ] as [$field, $label, $required])
                                <div>
                                    <label for="{{ $field }}" class="block text-sm font-medium text-[#374151]">{{ $label }}</label>
                                    <input id="{{ $field }}" name="{{ $field }}" type="text" value="{{ old($field, auth()->user()->{$field}) }}" @required($required) class="mt-2 w-full rounded-md border border-[#cfd7e2] px-3 py-3 text-sm outline-none transition focus:border-[#10223f] focus:ring-2 focus:ring-[#10223f]/10">
                                    @error($field)<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                                </div>
                            @endforeach

                            <div>
                                <label for="dob" class="block text-sm font-medium text-[#374151]">Date of birth</label>
                                <input id="dob" name="dob" type="date" value="{{ old('dob', auth()->user()->dob?->format('Y-m-d')) }}" required max="{{ now()->subYears(18)->toDateString() }}" class="mt-2 w-full rounded-md border border-[#cfd7e2] px-3 py-3 text-sm outline-none transition focus:border-[#10223f] focus:ring-2 focus:ring-[#10223f]/10">
                                @error('dob')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label for="pan_card" class="block text-sm font-medium text-[#374151]">PAN card</label>
                                <input id="pan_card" name="pan_card" type="text" value="{{ old('pan_card', auth()->user()->pan_card) }}" required maxlength="10" autocomplete="off" placeholder="ABCDE1234F" class="mt-2 w-full rounded-md border border-[#cfd7e2] px-3 py-3 text-sm uppercase tracking-[0.12em] outline-none transition focus:border-[#10223f] focus:ring-2 focus:ring-[#10223f]/10">
                                @error('pan_card')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label for="email" class="block text-sm font-medium text-[#374151]">Email</label>
                                <input id="email" name="email" type="email" value="{{ old('email', str_ends_with(auth()->user()->email, '@otp.settlepe.test') ? '' : auth()->user()->email) }}" required autocomplete="email" class="mt-2 w-full rounded-md border border-[#cfd7e2] px-3 py-3 text-sm outline-none transition focus:border-[#10223f] focus:ring-2 focus:ring-[#10223f]/10">
                                @error('email')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label for="income" class="block text-sm font-medium text-[#374151]">Monthly income</label>
                                <input id="income" name="income" type="number" min="0" max="1000000000" value="{{ old('income', auth()->user()->income) }}" required inputmode="numeric" class="mt-2 w-full rounded-md border border-[#cfd7e2] px-3 py-3 text-sm outline-none transition focus:border-[#10223f] focus:ring-2 focus:ring-[#10223f]/10">
                                @error('income')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>

                        </div>

                        <fieldset>
                            <legend class="block text-sm font-medium text-[#374151]">Gender</legend>
                            <div class="mt-2 grid grid-cols-2 gap-3">
                                @foreach (['male' => 'Male', 'female' => 'Female'] as $value => $label)
                                    <label class="flex cursor-pointer items-center gap-3 rounded-md border border-[#cfd7e2] px-3 py-3 text-sm text-[#374151] transition has-[:checked]:border-[#10223f] has-[:checked]:bg-[#eef4fb]">
                                        <input type="radio" name="gender" value="{{ $value }}" required @checked(old('gender', auth()->user()->gender) === $value) class="h-4 w-4 border-[#cfd7e2] text-[#10223f]">
                                        <span>{{ $label }}</span>
                                    </label>
                                @endforeach
                            </div>
                            @error('gender')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                        </fieldset>

                        <button type="submit" class="w-full rounded-md bg-[#10223f] px-4 py-3 text-sm font-semibold text-white transition hover:bg-[#18365f]">Continue to CRIF authentication</button>
                    </form>
                @endif
            </div>
        </div>
    </section>
@endsection
