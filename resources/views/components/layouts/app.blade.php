@php
    $user = auth()->user();
    $nav = [
        ['label' => 'Ringkasan', 'route' => 'dashboard', 'match' => 'dashboard', 'icon' => 'home'],
        ['label' => 'Program Donasi', 'route' => 'campaigns.index', 'match' => 'campaigns.*', 'icon' => 'programs'],
        ['label' => 'Riwayat Donasi', 'route' => 'donations.index', 'match' => 'donations.*', 'icon' => 'coins'],
        ['label' => 'Penyaluran Dana', 'route' => 'disbursements.index', 'match' => 'disbursements.*', 'icon' => 'send'],
    ];
    $audit = [
        ['label' => 'Verifikasi Integritas', 'route' => 'audit.integrity', 'icon' => 'shield'],
        ['label' => 'Log Aktivitas', 'route' => 'audit.activity', 'icon' => 'list'],
        ['label' => 'Log Login', 'route' => 'audit.login', 'icon' => 'login'],
        ['label' => 'Dashboard Anomali', 'route' => 'audit.anomalies', 'icon' => 'alert'],
    ];
    $reports = [
        ['label' => 'Laporan Donasi', 'route' => 'reports.donations', 'icon' => 'report'],
        ['label' => 'Laporan Penyaluran', 'route' => 'reports.disbursements', 'icon' => 'send'],
        ['label' => 'Ringkasan Saldo', 'route' => 'reports.balance', 'icon' => 'wallet'],
        ['label' => 'Cek Keaslian Laporan', 'route' => 'reports.verify', 'icon' => 'stamp'],
    ];
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    @include('components.layouts.partials-head')
    <title>{{ $title ?? 'Panel' }} — SIDONA</title>
</head>
<body class="staff bg-desk" x-data="{ menu: false }" @keydown.escape.window="menu = false">
    {{-- Mobile top bar --}}
    <header class="on-ink sticky top-0 z-30 flex h-14 items-center justify-between border-b-4 border-board bg-ink px-4 text-white lg:hidden">
        <a href="{{ route('dashboard') }}" wire:navigate class="paint-type text-2xl tracking-wide text-board">Sidona</a>
        <button type="button" @click="menu = true" class="flex h-11 w-11 items-center justify-center" aria-label="Buka menu" :aria-expanded="menu">
            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="square"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
        </button>
    </header>

    <div x-show="menu" x-cloak x-transition.opacity @click="menu = false" class="fixed inset-0 z-40 bg-ink/60 lg:hidden"></div>

    <aside
        class="on-ink fixed inset-y-0 left-0 z-50 flex w-64 -translate-x-full flex-col overflow-y-auto bg-ink text-white transition-transform duration-200 lg:translate-x-0"
        :class="menu && '!translate-x-0'"
        aria-label="Menu panel"
    >
        <div class="flex h-16 shrink-0 items-center justify-between border-b border-white/10 px-4">
            <a href="{{ route('dashboard') }}" wire:navigate class="paint-type text-3xl tracking-wide text-board">Sidona</a>
            <button type="button" @click="menu = false" class="flex h-11 w-11 items-center justify-center lg:hidden" aria-label="Tutup menu">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="square"><path d="M6 6l12 12M18 6 6 18"/></svg>
            </button>
        </div>

        <nav class="flex-1 pb-4" @click="menu = false">
            @foreach ($nav as $item)
                <a href="{{ route($item['route']) }}" wire:navigate class="nav-link" @if (request()->routeIs($item['match'])) aria-current="page" @endif>
                    <x-icon :name="$item['icon']" class="h-5 w-5" />
                    {{ $item['label'] }}
                </a>
            @endforeach

            @if ($user->isSuperAdmin())
                <p class="nav-heading">Sistem</p>
                <a href="{{ route('users.index') }}" wire:navigate class="nav-link" @if (request()->routeIs('users.*')) aria-current="page" @endif>
                    <x-icon name="users" class="h-5 w-5" />
                    Pengguna
                </a>
            @endif

            @if ($user->canAudit())
                <p class="nav-heading">Audit</p>
                @foreach ($audit as $item)
                    <a href="{{ route($item['route']) }}" wire:navigate class="nav-link" @if (request()->routeIs($item['route'])) aria-current="page" @endif>
                        <x-icon :name="$item['icon']" class="h-5 w-5" />
                        {{ $item['label'] }}
                    </a>
                @endforeach

                <p class="nav-heading">Laporan</p>
                @foreach ($reports as $item)
                    <a href="{{ route($item['route']) }}" wire:navigate class="nav-link" @if (request()->routeIs($item['route'])) aria-current="page" @endif>
                        <x-icon :name="$item['icon']" class="h-5 w-5" />
                        {{ $item['label'] }}
                    </a>
                @endforeach
            @endif
        </nav>

        <div class="shrink-0 border-t border-white/10 p-4">
            <a href="{{ route('profile') }}" wire:navigate @if (request()->routeIs('profile')) aria-current="page" @endif class="-mx-2 block px-2 py-1.5 transition-colors hover:bg-white/10 aria-[current=page]:bg-board aria-[current=page]:text-ink" title="Profil saya">
                <span class="block truncate text-sm font-bold">{{ $user->name }}</span>
                <span class="mt-0.5 block text-xs uppercase tracking-wider opacity-80">{{ $user->role->label() }} &middot; profil</span>
            </a>
            <div class="mt-4 flex items-center justify-between gap-3 text-sm">
                <a href="{{ route('program.index') }}" class="font-semibold text-white/70 underline-offset-4 hover:text-white hover:underline">Lihat situs</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-sm border-white/40 bg-transparent text-white hover:bg-board hover:text-ink">Keluar</button>
                </form>
            </div>
        </div>
    </aside>

    <main class="lg:pl-64">
        <div class="mx-auto max-w-6xl px-4 py-8 sm:px-8 lg:py-10">
            @if (session('status'))
                <div class="flash" role="status">
                    <svg class="mt-0.5 h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="square"><path d="M5 12.5 10 17.5 19 7"/></svg>
                    {{ session('status') }}
                </div>
            @endif

            {{ $slot }}
        </div>
    </main>

    @livewireScripts
</body>
</html>
