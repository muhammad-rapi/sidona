<!DOCTYPE html>
<html lang="id">
<head>
    @include('components.layouts.partials-head')
    <title>{{ $title ?? 'SIDONA — Sumbangan yang tercatat' }}</title>
</head>
<body class="flex min-h-screen flex-col bg-paper">
    @php
        $links = [
            ['label' => 'Program', 'route' => 'program.index', 'active' => ['program.index', 'program.show']],
            ['label' => 'Ajukan Program', 'route' => 'program.submit', 'active' => ['program.submit', 'program.proposal']],
            ['label' => 'FAQ', 'route' => 'faq', 'active' => ['faq', 'terms', 'privacy']],
            ['label' => 'Cek Donasi', 'route' => 'donations.check', 'active' => ['donations.check', 'donations.receipt']],
        ];
    @endphp
    <header class="on-ink print:hidden sticky top-0 z-30 border-b-4 border-board bg-ink text-white" x-data="{ menu: false }" @keydown.escape.window="menu = false">
        <nav class="mx-auto flex h-16 max-w-6xl items-center justify-between gap-4 px-5" aria-label="Navigasi utama">
            <a href="{{ route('program.index') }}" wire:navigate class="flex items-center gap-2.5" aria-label="SIDONA, beranda">
                <span class="relative block h-8 w-3.5 border-2 border-board bg-ink" aria-hidden="true">
                    <span class="absolute inset-x-0 bottom-0 h-1/2 bg-paint"></span>
                    <span class="absolute -bottom-2 left-1/2 h-4 w-4 -translate-x-1/2 rounded-full border-2 border-board bg-paint"></span>
                </span>
                <span class="paint-type text-3xl leading-none tracking-wide text-board">Sidona</span>
            </a>

            <div class="hidden items-center gap-3 text-[0.95rem] font-semibold md:flex">
                @foreach ($links as $link)
                    <a href="{{ route($link['route']) }}" wire:navigate @class(['whitespace-nowrap px-3 py-2 transition-colors hover:text-board', 'text-board' => request()->routeIs($link['active'])])>{{ $link['label'] }}</a>
                @endforeach
            </div>

            <button type="button" class="flex h-11 w-11 items-center justify-center md:hidden" @click="menu = !menu" :aria-expanded="menu" aria-controls="menu-publik" aria-label="Menu">
                <svg x-show="!menu" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="square"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
                <svg x-show="menu" x-cloak class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="square"><path d="M6 6l12 12M18 6 6 18"/></svg>
            </button>
        </nav>

        <div id="menu-publik" x-show="menu" x-cloak @click="menu = false" class="border-t border-white/15 bg-ink md:hidden">
            @foreach ($links as $link)
                <a href="{{ route($link['route']) }}" wire:navigate @class(['block border-b border-white/10 px-5 py-4 text-lg font-semibold', 'text-board' => request()->routeIs($link['active'])])>{{ $link['label'] }}</a>
            @endforeach
        </div>
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
                <p class="mt-3 max-w-md text-base leading-relaxed text-white/80">Setiap donasi yang masuk dicatat. Setiap rupiah yang keluar harus disetujui dua orang. Auditor bisa memeriksa semuanya kapan saja.</p>
            </div>
            <div class="text-sm leading-relaxed text-white/70 md:text-right">
                <p>Donasi sah begitu pembayaran masuk. Tidak ada yang menunggu persetujuan.</p>
                <p class="mt-4 flex flex-wrap gap-x-5 gap-y-1 md:justify-end">
                    <a href="{{ route('faq') }}" wire:navigate class="underline-offset-4 hover:text-board hover:underline">FAQ</a>
                    <a href="{{ route('support.new') }}" wire:navigate class="underline-offset-4 hover:text-board hover:underline">Hubungi kami</a>
                    <a href="{{ route('terms') }}" wire:navigate class="underline-offset-4 hover:text-board hover:underline">Syarat dan ketentuan</a>
                    <a href="{{ route('privacy') }}" wire:navigate class="underline-offset-4 hover:text-board hover:underline">Kebijakan privasi</a>
                </p>
                <p class="mt-3">&copy; {{ now()->year }} SIDONA</p>
            </div>
        </div>
    </footer>

    @livewireScripts
</body>
</html>
