@php
    $groups = App\Support\Faq::groups();
@endphp

<x-legal-page title="Pertanyaan umum" intro="Jawaban singkat untuk hal yang paling sering ditanyakan.">
    <div class="space-y-10">
        @foreach ($groups as $group => $items)
            <section aria-labelledby="g-{{ $loop->index }}">
                <h2 id="g-{{ $loop->index }}" class="paint-type mb-3 text-3xl">{{ $group }}</h2>
                <div class="border-t-2 border-ink">
                    @foreach ($items as [$question, $answer])
                        <details class="group border-b border-rule">
                            <summary class="flex min-h-12 cursor-pointer list-none items-center justify-between gap-4 py-3.5 text-left font-bold marker:hidden hover:text-paint-dark [&::-webkit-details-marker]:hidden">
                                <span>{{ $question }}</span>
                                <x-icon name="plus" class="shrink-0 transition-transform group-open:rotate-45" />
                            </summary>
                            <p class="pb-4 pr-8 text-ink-soft">{{ $answer }}</p>
                        </details>
                    @endforeach
                </div>
            </section>
        @endforeach
    </div>

    <p class="mt-10 border-t-2 border-ink pt-4 text-sm text-ink-soft">Belum terjawab? Baca juga <a href="{{ route('terms') }}" wire:navigate class="font-bold text-ink underline underline-offset-4">Syarat dan ketentuan</a> dan <a href="{{ route('privacy') }}" wire:navigate class="font-bold text-ink underline underline-offset-4">Kebijakan privasi</a>.</p>
</x-legal-page>
