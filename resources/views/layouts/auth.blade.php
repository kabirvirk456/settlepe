<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Settle Pe')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }
    </style>
</head>
<body class="min-h-screen bg-[#f6f7f2] text-[#111827]">
    <header class="border-b border-[#e5e7eb] bg-white">
        <div class="mx-auto flex max-w-6xl items-center justify-between px-4 py-4 md:px-6">
            <a href="{{ route('home') }}" class="text-sm font-semibold tracking-wide text-[#07142b]">
                SETTLE <span class="text-[#b7862d]">PE</span>
            </a>

            <nav class="hidden items-center gap-4 text-sm text-[#4b5563] sm:flex">
                <a href="{{ route('home') }}" class="hover:text-[#07142b]">Home</a>

                @auth
                    @if (auth()->user()->isTeamMember())
                        <a href="{{ route('team.home') }}" class="hover:text-[#07142b]">Team CRM</a>
                    @else
                        <a href="{{ route('dashboard') }}" class="hover:text-[#07142b]">{{ auth()->user()->service_fee_paid_at ? 'My case' : 'CRIF report' }}</a>
                    @endif
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="rounded-md bg-[#07142b] px-4 py-2 text-sm font-medium text-white hover:bg-[#0b2545]">
                            Logout
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="hover:text-[#07142b]">Login</a>
                    <a href="{{ route('register') }}" class="hover:text-[#07142b]">Register</a>
                    <a href="{{ route('team.login') }}" class="hover:text-[#07142b]">Team</a>
                    <a href="{{ route('register') }}" class="rounded-md bg-[#07142b] px-4 py-2 font-medium text-white hover:bg-[#0b2545]">
                        Start assessment
                    </a>
                @endauth
            </nav>

            <details class="relative sm:hidden">
                <summary class="grid h-11 w-11 cursor-pointer list-none place-items-center rounded-md border border-[#dfe5ec] text-[#07142b] [&::-webkit-details-marker]:hidden" aria-label="Open navigation">
                    <span class="space-y-1.5">
                        <span class="block h-0.5 w-5 bg-current"></span>
                        <span class="block h-0.5 w-5 bg-current"></span>
                        <span class="block h-0.5 w-5 bg-current"></span>
                    </span>
                </summary>
                <div class="absolute right-0 z-50 mt-2 w-48 rounded-lg border border-[#dfe5ec] bg-white p-2 text-sm shadow-lg">
                    <a href="{{ route('home') }}" class="block rounded-md px-3 py-2.5 text-[#4b5563] hover:bg-[#f7f9fb]">Home</a>
                    @auth
                        <a href="{{ auth()->user()->isTeamMember() ? route('team.home') : route('dashboard') }}" class="block rounded-md px-3 py-2.5 text-[#4b5563] hover:bg-[#f7f9fb]">
                            {{ auth()->user()->isTeamMember() ? 'Team CRM' : (auth()->user()->service_fee_paid_at ? 'My case' : 'CRIF report') }}
                        </a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="mt-1 w-full rounded-md bg-[#07142b] px-3 py-2.5 text-left font-medium text-white">Logout</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="block rounded-md px-3 py-2.5 text-[#4b5563] hover:bg-[#f7f9fb]">Login</a>
                        <a href="{{ route('register') }}" class="block rounded-md bg-[#07142b] px-3 py-2.5 font-medium text-white">Register</a>
                    @endauth
                </div>
            </details>
        </div>
    </header>

    <main class="flex min-h-[calc(100vh-73px)] items-start justify-center px-4 py-8 md:py-10">
        <div class="@yield('panel_width', 'w-full max-w-md')">
            @if (session('status'))
                <div class="mb-5 rounded-lg border border-[#bfeacc] bg-[#f0fbf4] px-4 py-3 text-sm font-semibold text-[#14783e] shadow-sm">
                    <div class="flex items-start gap-3">
                        <span class="mt-0.5 grid h-5 w-5 shrink-0 place-items-center rounded-full bg-[#dff7e7] text-[10px]">OK</span>
                        <span>{{ session('status') }}</span>
                    </div>
                </div>
            @endif

            @yield('content')
        </div>
    </main>
</body>
</html>
