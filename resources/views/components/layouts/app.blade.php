<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SIDONA</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-paper text-ink">
    <nav class="border-b border-line px-6 py-4 flex flex-wrap items-center justify-between gap-x-6 gap-y-3">
        <a href="{{ route('dashboard') }}" class="font-semibold tracking-tight">SIDONA</a>
        <div class="flex flex-wrap items-center gap-x-5 gap-y-2 text-sm">
            <a href="{{ route('campaigns.index') }}" wire:navigate class="text-ink-muted hover:text-ink">Program Donasi</a>
            <a href="{{ route('donations.index') }}" wire:navigate class="text-ink-muted hover:text-ink">Donasi</a>
            <a href="{{ route('disbursements.index') }}" wire:navigate class="text-ink-muted hover:text-ink">Penyaluran</a>
            @if (auth()->user()->isAuditor())
                <a href="{{ route('audit.integrity') }}" wire:navigate class="text-ink-muted hover:text-ink">Verifikasi Integritas</a>
                <a href="{{ route('audit.activity') }}" wire:navigate class="text-ink-muted hover:text-ink">Log Aktivitas</a>
                <a href="{{ route('audit.login') }}" wire:navigate class="text-ink-muted hover:text-ink">Log Login</a>
                <a href="{{ route('audit.anomalies') }}" wire:navigate class="text-ink-muted hover:text-ink">Dashboard Anomali</a>
                <a href="{{ route('reports.donations') }}" wire:navigate class="text-ink-muted hover:text-ink">Laporan Donasi</a>
                <a href="{{ route('reports.disbursements') }}" wire:navigate class="text-ink-muted hover:text-ink">Laporan Penyaluran</a>
                <a href="{{ route('reports.balance') }}" wire:navigate class="text-ink-muted hover:text-ink">Ringkasan Saldo</a>
                <a href="{{ route('reports.verify') }}" wire:navigate class="text-ink-muted hover:text-ink">Cek Keaslian Laporan</a>
            @endif
        </div>
        <div class="flex items-center gap-4 text-sm">
            <span class="text-ink-muted">{{ auth()->user()->name }} ({{ auth()->user()->role->label() }})</span>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="text-ink-muted hover:text-ink">Keluar</button>
            </form>
        </div>
    </nav>

    <main class="max-w-5xl mx-auto px-6 py-10">
        @if (session('status'))
            <div class="mb-6 border-l-2 border-verified bg-verified-soft px-4 py-3 text-verified text-sm">
                {{ session('status') }}
            </div>
        @endif

        {{ $slot }}
    </main>

    @livewireScripts
</body>
</html>
