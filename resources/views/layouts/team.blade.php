<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Settle Pe CRM')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root {
            --ink: #07142b;
            --muted: #64728a;
            --line: #dfe6f0;
            --panel: #ffffff;
            --wash: #f5f8fc;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background: var(--wash);
        }

        .crm-shell {
            min-height: 100vh;
            display: grid;
            grid-template-columns: 280px minmax(0, 1fr);
        }

        .crm-sidebar {
            min-height: 100vh;
            background: radial-gradient(circle at 18% 0%, rgba(51, 111, 255, .24), transparent 24%),
                linear-gradient(180deg, #071b34 0%, #031223 100%);
            color: #fff;
            position: sticky;
            top: 0;
        }

        .crm-card {
            border: 1px solid var(--line);
            background: var(--panel);
            border-radius: 8px;
            box-shadow: 0 10px 30px rgba(15, 35, 65, .05);
        }

        .crm-table th {
            color: #526071;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .02em;
            text-transform: uppercase;
        }

        .crm-table td,
        .crm-table th {
            border-bottom: 1px solid #edf1f6;
            padding: 14px 12px;
            white-space: nowrap;
        }

        .crm-table tr:last-child td {
            border-bottom: 0;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 12px;
            border-radius: 8px;
            padding: 12px 14px;
            color: rgba(255, 255, 255, .82);
            font-size: 14px;
            font-weight: 600;
        }

        .nav-item:hover,
        .nav-item-active {
            background: linear-gradient(135deg, #4464ff, #6246ea);
            color: #fff;
        }

        .nav-icon {
            width: 18px;
            height: 18px;
            display: grid;
            place-items: center;
            border: 1px solid rgba(255, 255, 255, .38);
            border-radius: 6px;
            font-size: 11px;
            line-height: 1;
        }

        .status-pill {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            padding: 5px 10px;
            font-size: 12px;
            font-weight: 700;
        }

        @media (max-width: 1023px) {
            .crm-shell {
                display: block;
            }

            .crm-sidebar {
                min-height: auto;
                position: relative;
                padding: 12px 16px;
            }

            .crm-sidebar > div:first-child {
                margin-bottom: 12px;
            }

            .crm-sidebar nav {
                display: flex;
                gap: 8px;
                overflow-x: auto;
                padding-bottom: 2px;
                scrollbar-width: thin;
            }

            .crm-sidebar .nav-item {
                min-height: 44px;
                flex: 0 0 auto;
                white-space: nowrap;
            }
        }

        @media (max-width: 639px) {
            .crm-table td,
            .crm-table th {
                padding: 12px 10px;
            }
        }
    </style>
</head>
<body class="text-[#07142b]">
    @php
        $teamUser = auth()->user();
        $roleLabel = $teamUser?->isAdmin() ? 'Super Admin' : ($teamUser?->isRm() ? 'Relationship Manager' : 'Sales Executive');
        $avatarInitial = strtoupper(substr($teamUser?->name ?? 'T', 0, 1));
        if ($teamUser?->isAdmin()) {
            $navItems = [
                ['label' => 'Dashboard', 'href' => route('team.admin'), 'active' => request()->routeIs('team.admin'), 'icon' => 'D'],
                ['label' => 'Sales Queue', 'href' => route('team.sales'), 'active' => request()->routeIs('team.sales'), 'icon' => 'S'],
                ['label' => 'RM Cases', 'href' => route('team.rm'), 'active' => request()->routeIs('team.rm'), 'icon' => 'R'],
            ];
        } elseif ($teamUser?->isRm()) {
            $navItems = [
                ['label' => 'RM Cases', 'href' => route('team.rm'), 'active' => request()->routeIs('team.rm'), 'icon' => 'R'],
            ];
        } else {
            $navItems = [
                ['label' => 'Sales Queue', 'href' => route('team.sales'), 'active' => request()->routeIs('team.sales'), 'icon' => 'S'],
            ];
        }
    @endphp

    <div class="crm-shell">
        <aside class="crm-sidebar flex flex-col px-4 py-5">
            <div class="mb-8 flex items-center gap-3 px-2">
                <div class="grid h-10 w-10 place-items-center rounded-lg bg-gradient-to-br from-[#30d4ff] to-[#635bff] text-xl font-black">S</div>
                <div>
                    <p class="text-lg font-bold leading-tight">Settle Pe CRM</p>
                    <p class="text-xs text-white/55">Settle Pe workspace</p>
                </div>
            </div>

            <nav class="space-y-2">
                @foreach ($navItems as $item)
                    <a href="{{ $item['href'] }}" class="nav-item {{ $item['active'] ? 'nav-item-active' : '' }}">
                        <span class="nav-icon">{{ $item['icon'] }}</span>
                        <span>{{ $item['label'] }}</span>
                    </a>
                @endforeach
            </nav>

            <div class="mt-auto hidden rounded-lg border border-white/10 bg-white/5 p-3 lg:block">
                <div class="flex items-center gap-3">
                    <div class="grid h-10 w-10 shrink-0 place-items-center rounded-full bg-white/12 text-sm font-black text-white ring-1 ring-white/20">{{ $avatarInitial }}</div>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-semibold">{{ $teamUser?->name }}</p>
                        <p class="truncate text-xs text-white/60">{{ $roleLabel }}</p>
                    </div>
                </div>
            </div>
        </aside>

        <div class="min-w-0">
            <header class="sticky top-0 z-20 border-b border-[#dfe6f0] bg-white/95 backdrop-blur">
                <div class="flex flex-col gap-4 px-4 py-4 md:flex-row md:items-center md:justify-between lg:px-6">
                    <div class="flex items-center gap-4">
                        <div class="grid h-10 w-10 place-items-center rounded-lg border border-[#dfe6f0] text-lg font-semibold lg:hidden">S</div>
                        <div>
                            <h1 class="text-lg font-bold text-[#07142b]">@yield('page_title', 'Welcome back, '.$teamUser?->name)</h1>
                            <p class="mt-1 text-sm text-[#64728a]">@yield('page_subtitle', "Here's what's happening with your cases today.")</p>
                        </div>
                    </div>

                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                        <form method="GET" action="@yield('search_action', route('team.home'))" class="relative">
                            <input
                                name="search"
                                value="{{ request('search') }}"
                                placeholder="Search cases, customers..."
                                class="h-11 w-full rounded-lg border border-[#dfe6f0] bg-white pl-10 pr-4 text-sm outline-none transition focus:border-[#4b63ff] focus:ring-2 focus:ring-[#4b63ff]/10 sm:w-[300px]"
                            >
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs font-bold text-[#72819a]">S</span>
                        </form>

                        <div class="hidden h-8 w-px bg-[#dfe6f0] sm:block"></div>

                        <div class="flex items-center justify-between gap-3">
                            <div class="grid h-10 place-items-center rounded-lg border border-[#dfe6f0] bg-white px-3 text-xs font-bold uppercase tracking-wide text-[#526071]">
                                {{ $teamUser?->role }}
                            </div>
                            <div class="grid h-10 w-10 shrink-0 place-items-center rounded-full bg-[#10223f] text-sm font-black text-white">{{ $avatarInitial }}</div>
                            <div class="hidden sm:block">
                                <p class="text-sm font-semibold text-[#07142b]">{{ $teamUser?->name }}</p>
                                <p class="text-xs text-[#64728a]">{{ $roleLabel }}</p>
                            </div>
                            <form method="POST" action="{{ route('team.logout') }}">
                                @csrf
                                <button type="submit" class="rounded-lg border border-[#dfe6f0] px-3 py-2 text-sm font-semibold text-[#526071] transition hover:bg-[#f5f8fc]">Logout</button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            <main class="px-4 py-5 lg:px-6">
                @if (session('status'))
                    <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm font-semibold text-green-700">
                        {{ session('status') }}
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
