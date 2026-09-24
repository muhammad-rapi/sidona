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
    <nav class="border-b border-line px-6 py-4 flex items-center justify-between">
        <a href="{{ route('program.index') }}" class="font-semibold tracking-tight">SIDONA</a>
        <div class="flex items-center gap-5 text-sm">
            <a href="{{ route('donations.check') }}" wire:navigate class="text-ink-muted hover:text-ink">Cek Status Donasi</a>
            <a href="{{ route('login') }}" wire:navigate class="text-ink-muted hover:text-ink">Masuk Staf</a>
        </div>
    </nav>

    <main class="max-w-5xl mx-auto px-6 py-10">
        {{ $slot }}
    </main>

    @livewireScripts
</body>
</html>
