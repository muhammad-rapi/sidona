<!DOCTYPE html>
<html lang="id">
<head>
    @include('components.layouts.partials-head')
    <title>Masuk — SIDONA</title>
</head>
<body class="on-ink min-h-screen bg-ink text-white">
    {{ $slot }}
    @livewireScripts
</body>
</html>
