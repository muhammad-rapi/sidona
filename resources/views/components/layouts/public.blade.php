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
        <a href="{{ route('program.index') }}" class="font-semibold">SIDONA</a>
        <div class="flex items-center gap-4 text-sm">
            <a href="{{ route('login') }}" wire:navigate>Masuk Staf</a>
        </div>
    </nav>

    <main class="max-w-5xl mx-auto px-6 py-8">
        {{ $slot }}
    </main>

    @livewireScripts
</body>
</html>
