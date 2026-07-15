@extends('layouts.auth')

@section('title', 'Team Login | Settle Pe')
@section('panel_width', 'w-full max-w-4xl')

@section('content')
    <section class="grid overflow-hidden rounded-lg border border-[#dfe5ec] bg-white shadow-sm md:grid-cols-[0.9fr_1.1fr]">
        <div class="bg-[#10223f] px-6 py-8 text-white md:px-8">
            <p class="text-xs font-semibold uppercase tracking-wide text-[#f4c877]">Internal CRM</p>
            <h1 class="mt-3 text-3xl font-semibold leading-tight">Sales, RM, and Admin workspace.</h1>
            <p class="mt-4 text-sm leading-6 text-[#d8e2ee]">
                Manage customers who fetched a CRIF report, paid Rs. 99, and moved into the settlement service.
            </p>

            <div class="mt-8 rounded-lg border border-white/15 bg-white/5 p-4 text-sm text-[#edf4ff]">
                <p class="font-semibold text-white">Authorized team access</p>
                <p class="mt-2 leading-6 text-[#d8e2ee]">Use the credentials assigned by your Settle Pe administrator to access the CRM workspace.</p>
            </div>
        </div>

        <form method="POST" action="{{ route('team.login.store') }}" class="space-y-5 px-6 py-8 md:px-8">
            @csrf

            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-[#b7862d]">Team login</p>
                <h2 class="mt-2 text-2xl font-semibold text-[#111827]">Open CRM</h2>
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-[#374151]">Email</label>
                <input
                    id="email"
                    name="email"
                    type="email"
                    value="{{ old('email') }}"
                    required
                    autofocus
                    autocomplete="email"
                    class="mt-2 w-full rounded-md border border-[#cfd7e2] px-3 py-3 text-sm outline-none transition focus:border-[#10223f] focus:ring-2 focus:ring-[#10223f]/10"
                >
                @error('email')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-[#374151]">Password</label>
                <input
                    id="password"
                    name="password"
                    type="password"
                    required
                    autocomplete="current-password"
                    class="mt-2 w-full rounded-md border border-[#cfd7e2] px-3 py-3 text-sm outline-none transition focus:border-[#10223f] focus:ring-2 focus:ring-[#10223f]/10"
                >
                @error('password')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <label class="flex items-center gap-2 text-sm text-[#4b5563]">
                <input type="checkbox" name="remember" value="1" class="rounded border-[#cfd7e2]">
                Remember me
            </label>

            <button type="submit" class="w-full rounded-md bg-[#10223f] px-4 py-3 text-sm font-semibold text-white transition hover:bg-[#18365f]">
                Login to CRM
            </button>
        </form>
    </section>
@endsection
