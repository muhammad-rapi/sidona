<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SIDONA</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-lavender-mist text-ink-black">
    <nav class="bg-paper-white border-b border-frost-gray px-6 py-4 flex flex-wrap items-center justify-between gap-x-6 gap-y-3">
        <a href="{{ route('dashboard') }}" class="font-medium tracking-tight text-graphite">SIDONA</a>
        <div class="flex flex-wrap items-center gap-x-1 gap-y-2 text-sm">
            <a href="{{ route('campaigns.index') }}" wire:navigate class="rounded-full px-3 py-1.5 text-graphite transition-all duration-200 hover:bg-mint-wash hover:text-coral-pulse active:scale-95">Program Donasi</a>
            <a href="{{ route('donations.index') }}" wire:navigate class="rounded-full px-3 py-1.5 text-graphite transition-all duration-200 hover:bg-mint-wash hover:text-coral-pulse active:scale-95">Donasi</a>
            <a href="{{ route('disbursements.index') }}" wire:navigate class="rounded-full px-3 py-1.5 text-graphite transition-all duration-200 hover:bg-mint-wash hover:text-coral-pulse active:scale-95">Penyaluran</a>

            @if (auth()->user()->isAuditor())
                <div x-data="{ open: false }" @click.outside="open = false" class="relative">
                    <button
                        type="button"
                        @click="open = !open"
                        class="flex items-center gap-1 rounded-full px-3 py-1.5 text-graphite transition-all duration-200 hover:bg-mint-wash hover:text-coral-pulse active:scale-95"
                    >
                        Audit
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 transition-transform duration-200" :class="open && 'rotate-180'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                    </button>
                    <div
                        x-show="open"
                        x-transition:enter="transition ease-out duration-150"
                        x-transition:enter-start="opacity-0 scale-95"
                        x-transition:enter-end="opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-100"
                        x-transition:leave-start="opacity-100 scale-100"
                        x-transition:leave-end="opacity-0 scale-95"
                        class="absolute left-0 z-20 mt-2 w-56 origin-top-left rounded-2xl border border-frost-gray bg-white p-2 shadow-lg"
                        style="display: none"
                    >
                        <a href="{{ route('audit.integrity') }}" wire:navigate @click="open = false" class="block rounded-lg px-3 py-2 text-graphite transition-colors duration-150 hover:bg-mint-wash hover:text-canopy-green">Verifikasi Integritas</a>
                        <a href="{{ route('audit.activity') }}" wire:navigate @click="open = false" class="block rounded-lg px-3 py-2 text-graphite transition-colors duration-150 hover:bg-mint-wash hover:text-canopy-green">Log Aktivitas</a>
                        <a href="{{ route('audit.login') }}" wire:navigate @click="open = false" class="block rounded-lg px-3 py-2 text-graphite transition-colors duration-150 hover:bg-mint-wash hover:text-canopy-green">Log Login</a>
                        <a href="{{ route('audit.anomalies') }}" wire:navigate @click="open = false" class="block rounded-lg px-3 py-2 text-graphite transition-colors duration-150 hover:bg-mint-wash hover:text-canopy-green">Dashboard Anomali</a>
                    </div>
                </div>

                <div x-data="{ open: false }" @click.outside="open = false" class="relative">
                    <button
                        type="button"
                        @click="open = !open"
                        class="flex items-center gap-1 rounded-full px-3 py-1.5 text-graphite transition-all duration-200 hover:bg-mint-wash hover:text-coral-pulse active:scale-95"
                    >
                        Laporan
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 transition-transform duration-200" :class="open && 'rotate-180'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                    </button>
                    <div
                        x-show="open"
                        x-transition:enter="transition ease-out duration-150"
                        x-transition:enter-start="opacity-0 scale-95"
                        x-transition:enter-end="opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-100"
                        x-transition:leave-start="opacity-100 scale-100"
                        x-transition:leave-end="opacity-0 scale-95"
                        class="absolute left-0 z-20 mt-2 w-56 origin-top-left rounded-2xl border border-frost-gray bg-white p-2 shadow-lg"
                        style="display: none"
                    >
                        <a href="{{ route('reports.donations') }}" wire:navigate @click="open = false" class="block rounded-lg px-3 py-2 text-graphite transition-colors duration-150 hover:bg-mint-wash hover:text-canopy-green">Laporan Donasi</a>
                        <a href="{{ route('reports.disbursements') }}" wire:navigate @click="open = false" class="block rounded-lg px-3 py-2 text-graphite transition-colors duration-150 hover:bg-mint-wash hover:text-canopy-green">Laporan Penyaluran</a>
                        <a href="{{ route('reports.balance') }}" wire:navigate @click="open = false" class="block rounded-lg px-3 py-2 text-graphite transition-colors duration-150 hover:bg-mint-wash hover:text-canopy-green">Ringkasan Saldo</a>
                        <a href="{{ route('reports.verify') }}" wire:navigate @click="open = false" class="block rounded-lg px-3 py-2 text-graphite transition-colors duration-150 hover:bg-mint-wash hover:text-canopy-green">Cek Keaslian Laporan</a>
                    </div>
                </div>
            @endif
        </div>
        <div class="flex items-center gap-4 text-sm">
            <span class="text-graphite">{{ auth()->user()->name }} ({{ auth()->user()->role->label() }})</span>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="rounded-full px-3 py-1.5 text-graphite transition-all duration-200 hover:bg-mint-wash hover:text-coral-pulse active:scale-95">Keluar</button>
            </form>
        </div>
    </nav>

    <main class="max-w-5xl mx-auto px-6 py-10">
        @if (session('status'))
            <div class="mb-6 border-l-2 border-leaf-bright bg-mint-wash px-4 py-3 text-leaf-bright text-sm">
                {{ session('status') }}
            </div>
        @endif

        {{ $slot }}
    </main>

    @livewireScripts
</body>
</html>
