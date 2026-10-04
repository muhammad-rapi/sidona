@php
    $featured = $campaigns->onFirstPage() ? $campaigns->first() : null;
    $others = $featured ? $campaigns->slice(1) : $campaigns;
@endphp

<div>
    @if ($featured)
        @php
            $raised = (int) $featured->raised_sum;
            $percent = $featured->progressPercent($raised);
        @endphp
        <section class="on-board border-b-4 border-ink bg-board" aria-labelledby="featured-title">
            <div class="mx-auto grid max-w-6xl lg:grid-cols-[1.05fr_1fr]">
                <a href="{{ route('program.show', $featured) }}" wire:navigate class="relative block aspect-[4/3] overflow-hidden bg-ink lg:aspect-auto lg:min-h-[34rem]" aria-label="Lihat program {{ $featured->name }}">
                    @if ($featured->cover_image)
                        <img src="{{ Illuminate\Support\Facades\Storage::url($featured->cover_image) }}" alt="" class="absolute inset-0 h-full w-full object-cover">
                    @else
                        <div class="paint-type absolute inset-0 flex items-center justify-center text-[10rem] text-board/30">{{ mb_substr($featured->name, 8, 1) }}</div>
                    @endif
                </a>

                <div class="flex flex-col justify-center px-5 py-10 lg:px-12 lg:py-14">
                    <p class="text-sm font-semibold text-ink-soft">{{ $featured->account_holder ?: 'Program SIDONA' }}</p>
                    <h1 id="featured-title" class="paint-type mt-1 text-5xl leading-[0.95] text-ink sm:text-6xl">{{ $featured->name }}</h1>
                    <p class="mt-4 line-clamp-3 max-w-md text-base leading-relaxed text-ink">{{ $featured->description }}</p>

                    <div class="mt-8">
                        <p class="paint-type text-[2.6rem] leading-none text-ink sm:text-6xl">Rp&nbsp;{{ number_format($raised, 0, ',', '.') }}</p>
                        <p class="mt-1 text-sm font-semibold text-ink">terkumpul dari Rp&nbsp;{{ number_format($featured->target_amount, 0, ',', '.') }}</p>
                        <div class="thermo-h thermo-h-rise mt-4 !h-5" style="--level: {{ $percent }}" role="img" aria-label="Terkumpul {{ $percent }} persen"><i></i></div>
                        <p class="mt-2 text-sm text-ink-soft">{{ number_format($featured->donor_count, 0, ',', '.') }} donatur &middot; sisa {{ $featured->daysLeft() }} hari</p>
                    </div>

                    <div class="mt-8 flex flex-wrap items-center gap-x-6 gap-y-3">
                        <a href="{{ route('program.show', $featured) }}" wire:navigate class="btn btn-paint btn-lg">Donasi sekarang</a>
                        <a href="#semua-program" class="text-sm font-semibold underline decoration-2 underline-offset-4 hover:text-paint-dark">Program lain</a>
                    </div>
                    <p class="mt-5 text-sm text-ink-soft">Bayar lewat QRIS, VA, atau e-wallet. Kuitansi langsung terbit.</p>
                </div>
            </div>
        </section>
    @else
        <section class="on-board bg-board">
            <div class="mx-auto max-w-6xl px-5 py-20">
                <h1 class="paint-type text-6xl text-ink sm:text-8xl">Belum ada papan terpasang</h1>
                <p class="mt-4 max-w-md text-lg text-ink">Program donasi aktif akan tampil di sini begitu dibuka.</p>
            </div>
            <div class="h-1 bg-ink"></div>
        </section>
    @endif

    <section id="semua-program" class="mx-auto max-w-6xl px-5 py-14">
        @if ($others->isNotEmpty())
            <h2 class="paint-type text-4xl text-ink sm:text-5xl">Program lain yang sedang berjalan</h2>

            <ul class="mt-6 border-t-2 border-ink">
                @foreach ($others as $campaign)
                    @php
                        $campaignRaised = (int) $campaign->raised_sum;
                        $campaignPercent = $campaign->progressPercent($campaignRaised);
                    @endphp
                    <li class="border-b-2 border-ink">
                        <a href="{{ route('program.show', $campaign) }}" wire:navigate class="group grid items-center gap-x-6 gap-y-4 py-6 sm:grid-cols-[7rem_1fr_auto] md:grid-cols-[9rem_1fr_16rem_auto]">
                            <div class="aspect-[4/3] overflow-hidden border-2 border-ink bg-board sm:aspect-square md:aspect-[4/3]">
                                @if ($campaign->cover_image)
                                    <img src="{{ Illuminate\Support\Facades\Storage::url($campaign->cover_image) }}" alt="" class="h-full w-full object-cover" loading="lazy">
                                @else
                                    <div class="paint-type flex h-full w-full items-center justify-center text-5xl text-ink/80">{{ mb_substr($campaign->name, 0, 1) }}</div>
                                @endif
                            </div>

                            <div class="min-w-0">
                                <h3 class="paint-type text-3xl leading-none text-ink group-hover:text-paint-dark">{{ $campaign->name }}</h3>
                                <p class="mt-2 line-clamp-2 text-sm text-ink-soft">{{ $campaign->description }}</p>
                            </div>

                            <div class="sm:col-span-2 md:col-span-1">
                                <div class="thermo-h thermo-h-rise" style="--level: {{ $campaignPercent }}"><i></i></div>
                                <div class="mt-2 flex items-baseline justify-between gap-3 text-sm">
                                    <span class="font-extrabold text-paint-dark">Rp&nbsp;{{ number_format($campaignRaised, 0, ',', '.') }}</span>
                                    <span class="font-bold text-ink">{{ $campaignPercent }}%</span>
                                </div>
                            </div>

                            <span class="btn btn-ink hidden md:inline-flex">Donasi</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif

        <div class="mt-10">{{ $campaigns->links() }}</div>
    </section>
</div>
