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
    <nav class="bg-paper-white border-b border-frost-gray px-6 py-4 flex items-center justify-between">
        <a href="{{ route('program.index') }}" wire:navigate class="font-medium tracking-tight text-graphite">SIDONA</a>
        <a href="{{ route('program.index') }}" wire:navigate class="text-sm text-graphite hover:text-coral-pulse transition-colors duration-200">Home</a>
    </nav>

    {{ $slot }}
    @livewireScripts
</body>
</html>
