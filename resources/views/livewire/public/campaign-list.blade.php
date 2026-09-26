<div>
    <div class="animate-fade-in-up-blur mb-14">
        <span class="inline-flex items-center rounded-full bg-mint-wash px-3 py-1 text-[10px] font-semibold uppercase tracking-[0.2em] text-canopy-green">Donasi Terbuka</span>
        <h1 class="mt-4 text-4xl font-semibold tracking-tight text-ink-black sm:text-5xl">Program Donasi Aktif</h1>
        <p class="mt-3 max-w-md text-base text-graphite">Pilih program yang ingin Anda bantu, transfer, lalu unggah bukti dalam hitungan menit.</p>
    </div>

    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
        @forelse ($campaigns as $campaign)
            @php
                $raised = $campaign->verifiedDonationsTotal();
                $percent = $campaign->target_amount > 0 ? min(100, (int) round($raised / $campaign->target_amount * 100)) : 0;
                $featured = $loop->first && $campaigns->count() > 1;
            @endphp
            <a
                href="{{ route('program.show', $campaign) }}"
                wire:navigate
                class="group animate-fade-in-up-blur block rounded-[2rem] bg-mint-wash/40 p-1.5 ring-1 ring-black/5 transition-all duration-500 [transition-timing-function:cubic-bezier(0.32,0.72,0,1)] hover:-translate-y-1 hover:bg-mint-wash/70 {{ $featured ? 'sm:col-span-2 lg:col-span-2 lg:row-span-2' : '' }}"
                style="animation-delay: {{ min($loop->index, 6) * 70 }}ms"
            >
                <div class="flex h-full flex-col rounded-[calc(2rem-0.375rem)] bg-white p-7 shadow-[inset_0_1px_1px_rgba(255,255,255,0.9)] {{ $featured ? 'justify-center' : '' }}">
                    <span class="inline-flex w-fit items-center rounded-full bg-mint-wash px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.15em] text-canopy-green">{{ $percent }}% Terkumpul</span>

                    <h2 class="mt-4 font-semibold tracking-tight text-ink-black transition-colors duration-300 group-hover:text-canopy-green {{ $featured ? 'text-2xl' : 'text-lg' }}">{{ $campaign->name }}</h2>
                    <p class="mt-2 text-sm leading-relaxed text-graphite {{ $featured ? 'line-clamp-3' : 'line-clamp-2' }}">{{ $campaign->description }}</p>

                    <div class="mt-6">
                        <div class="h-2 overflow-hidden rounded-full bg-mint-wash">
                            <div class="h-full rounded-full bg-coral-pulse transition-all duration-700 [transition-timing-function:cubic-bezier(0.32,0.72,0,1)]" style="width: {{ $percent }}%"></div>
                        </div>
                        <div class="mt-2 flex items-baseline justify-between text-sm">
                            <span class="font-medium text-canopy-green">Rp {{ number_format($raised, 0, ',', '.') }}</span>
                            <span class="text-graphite">dari Rp {{ number_format($campaign->target_amount, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <span class="mt-5 inline-flex items-center gap-2 text-sm font-medium text-coral-pulse">
                        Lihat Program
                        <span class="flex h-6 w-6 items-center justify-center rounded-full bg-mint-wash transition-transform duration-500 [transition-timing-function:cubic-bezier(0.32,0.72,0,1)] group-hover:translate-x-1">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
                        </span>
                    </span>
                </div>
            </a>
        @empty
            <p class="text-graphite sm:col-span-2 lg:col-span-3">Belum ada program donasi aktif.</p>
        @endforelse
    </div>

    <div class="mt-10">{{ $campaigns->links() }}</div>
</div>
