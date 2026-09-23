<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SIDONA</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-slate-100 text-slate-900">
    <nav class="bg-slate-900 text-white px-6 py-4 flex items-center justify-between">
        <a href="{{ route('dashboard') }}" class="font-semibold">SIDONA</a>
        <div class="flex items-center gap-4 text-sm">
            <a href="{{ route('campaigns.index') }}" wire:navigate>Program Donasi</a>
            <span class="text-slate-400">{{ auth()->user()->name }} ({{ auth()->user()->role->label() }})</span>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit">Keluar</button>
            </form>
        </div>
    </nav>

    <main class="max-w-5xl mx-auto px-6 py-8">
        @if (session('status'))
            <div class="mb-4 rounded border border-emerald-600 bg-emerald-50 px-4 py-3 text-emerald-800">
                {{ session('status') }}
            </div>
        @endif

        {{ $slot }}
    </main>

    @livewireScripts
</body>
</html>
