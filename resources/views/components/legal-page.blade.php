@props(['title', 'intro' => null, 'updated' => null])

<x-layouts.public :title="$title.' — SIDONA'">
    <div class="mx-auto grid max-w-6xl gap-x-16 gap-y-8 px-5 py-14 lg:grid-cols-[18rem_1fr]">
        <header class="lg:sticky lg:top-24 lg:self-start">
            <h1 class="paint-type text-5xl text-ink sm:text-6xl">{{ $title }}</h1>
            @if ($intro)
                <p class="mt-5 text-lg text-ink">{{ $intro }}</p>
            @endif
            @if ($updated)
                <p class="mt-4 border-t-2 border-ink pt-3 text-sm text-ink-soft">Terakhir diperbarui {{ $updated }}</p>
            @endif
            <nav class="mt-6 flex flex-col gap-1 text-sm font-semibold" aria-label="Halaman informasi">
                <a href="{{ route('faq') }}" wire:navigate @class(['py-1 hover:text-paint-dark', 'text-paint-dark underline underline-offset-4' => request()->routeIs('faq')])>Pertanyaan umum</a>
                <a href="{{ route('terms') }}" wire:navigate @class(['py-1 hover:text-paint-dark', 'text-paint-dark underline underline-offset-4' => request()->routeIs('terms')])>Syarat dan ketentuan</a>
                <a href="{{ route('privacy') }}" wire:navigate @class(['py-1 hover:text-paint-dark', 'text-paint-dark underline underline-offset-4' => request()->routeIs('privacy')])>Kebijakan privasi</a>
            </nav>
        </header>

        <div class="min-w-0 max-w-prose text-[1.05rem] leading-relaxed text-ink">
            {{ $slot }}
        </div>
    </div>
</x-layouts.public>
