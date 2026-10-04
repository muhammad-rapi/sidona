<!DOCTYPE html>
<html lang="id">
<head>
    @include('components.layouts.partials-head')
    <title>{{ $title ?? 'SIDONA — Sumbangan yang tercatat' }}</title>
</head>
<body class="flex min-h-screen flex-col bg-paper">
    <header class="on-ink print:hidden sticky top-0 z-30 border-b-4 border-board bg-ink text-white">
        <nav class="mx-auto flex h-16 max-w-6xl items-center justify-between gap-4 px-5" aria-label="Navigasi utama">
            <a href="{{ route('program.index') }}" wire:navigate class="flex items-center gap-2.5" aria-label="SIDONA, beranda">
                <span class="relative block h-8 w-3.5 border-2 border-board bg-ink" aria-hidden="true">
                    <span class="absolute inset-x-0 bottom-0 h-1/2 bg-paint"></span>
                    <span class="absolute -bottom-2 left-1/2 h-4 w-4 -translate-x-1/2 rounded-full border-2 border-board bg-paint"></span>
                </span>
                <span class="paint-type text-3xl leading-none tracking-wide text-board">Sidona</span>
            </a>
            <div class="flex items-center gap-1 text-[0.95rem] font-semibold sm:gap-3">
                <a href="{{ route('program.index') }}" wire:navigate @class(['px-3 py-2 transition-colors hover:text-board', 'text-board' => request()->routeIs('program.*')])>Program</a>
                <a href="{{ route('program.submit') }}" wire:navigate @class(['px-3 py-2 transition-colors hover:text-board', 'text-board' => request()->routeIs('program.submit')])>Ajukan Program</a>
                <a href="{{ route('donations.check') }}" wire:navigate @class(['px-3 py-2 transition-colors hover:text-board', 'text-board' => request()->routeIs('donations.check', 'donations.receipt')])>Cek Donasi</a>
            </div>
        </nav>
    </header>

    <main class="flex-1">
        @if (session('status'))
            <div class="on-board border-b-4 border-ink bg-paid-wash px-5 py-3 text-center text-sm font-bold text-paid" role="status">{{ session('status') }}</div>
        @endif

        {{ $slot }}
    </main>

    <footer class="on-ink print:hidden border-t-4 border-board bg-ink text-white">
        <div class="mx-auto grid max-w-6xl gap-8 px-5 py-12 md:grid-cols-[1.2fr_1fr]">
            <div>
                <p class="paint-type text-4xl text-board">Sidona</p>
                <p class="mt-3 max-w-md text-base leading-relaxed text-white/80">Sistem Informasi Donasi dan Audit. Setiap donasi yang masuk dan setiap rupiah yang disalurkan dicatat di log berantai, bisa diperiksa auditor kapan saja.</p>
            </div>
            <div class="text-sm leading-relaxed text-white/70 md:text-right">
                <p>Donasi dikonfirmasi otomatis begitu pembayaran diterima. Tidak ada yang perlu menunggu persetujuan siapa pun.</p>
                <p class="mt-3">&copy; {{ now()->year }} SIDONA</p>
            </div>
        </div>
    </footer>

    @livewireScripts
</body>
</html>
