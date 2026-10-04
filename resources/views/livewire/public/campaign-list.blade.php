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
        <section class="on-board bg-board" aria-labelledby="featured-title">
            <div class="mx-auto grid max-w-6xl grid-cols-[1fr_auto] items-center gap-x-6 gap-y-8 px-5 py-10 sm:gap-x-12 md:py-16 lg:gap-x-16">
                <div class="rise-in min-w-0">
                    <p class="text-sm font-bold uppercase tracking-wider text-ink-soft">{{ $featured->account_holder ?: 'Program SIDONA' }}</p>
                    <h1 id="featured-title" class="paint-type mt-2 text-[2.6rem] leading-[0.95] text-ink sm:text-6xl lg:text-7xl">{{ $featured->name }}</h1>

                    <p class="mt-8 text-sm font-bold uppercase tracking-wider text-ink-soft">Terkumpul</p>
                    <p class="paint-type text-[2.2rem] leading-none text-paint sm:text-7xl lg:text-8xl">Rp&nbsp;{{ number_format($raised, 0, ',', '.') }}</p>
                    <p class="mt-3 text-base font-semibold text-ink">dari target Rp&nbsp;{{ number_format($featured->target_amount, 0, ',', '.') }}</p>

                    <p class="mt-2 text-sm text-ink-soft">{{ number_format($featured->donor_count, 0, ',', '.') }} donatur &middot; sisa {{ $featured->daysLeft() }} hari</p>

                    <div class="mt-8 flex flex-wrap items-center gap-4">
                        <a href="{{ route('program.show', $featured) }}" wire:navigate class="btn btn-paint btn-lg">Donasi sekarang</a>
                        <a href="#semua-program" class="text-sm font-bold uppercase tracking-wider text-ink underline decoration-2 underline-offset-4 hover:text-paint-dark">Lihat program lain</a>
                    </div>
                </div>

                <x-thermometer :percent="$percent" rise class="[--thermo-h:16rem] sm:[--thermo-h:22rem] lg:[--thermo-h:28rem]" />
            </div>
            <div class="stripe"></div>
        </section>
    @else
        <section class="on-board bg-board">
            <div class="mx-auto max-w-6xl px-5 py-20">
                <h1 class="paint-type text-6xl text-ink sm:text-8xl">Belum ada papan terpasang</h1>
                <p class="mt-4 max-w-md text-lg text-ink">Program donasi aktif akan tampil di sini begitu dibuka.</p>
            </div>
            <div class="stripe"></div>
        </section>
    @endif

    <section id="semua-program" class="mx-auto max-w-6xl px-5 py-14">
        <ol class="mb-12 grid gap-px border-2 border-ink bg-ink text-ink sm:grid-cols-3" aria-label="Cara berdonasi">
            <li class="flex items-baseline gap-3 bg-board-wash px-5 py-4"><span class="paint-type text-4xl text-paint">1</span><span class="font-bold">Pilih program dan nominal</span></li>
            <li class="flex items-baseline gap-3 bg-board-wash px-5 py-4"><span class="paint-type text-4xl text-paint">2</span><span class="font-bold">Bayar lewat QRIS, VA, atau e-wallet</span></li>
            <li class="flex items-baseline gap-3 bg-board-wash px-5 py-4"><span class="paint-type text-4xl text-paint">3</span><span class="font-bold">Kuitansi langsung terbit, tanpa menunggu</span></li>
        </ol>

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
