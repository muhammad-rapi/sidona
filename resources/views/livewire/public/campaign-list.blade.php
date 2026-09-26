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
            @endphp
            <a
                href="{{ route('program.show', $campaign) }}"
                wire:navigate
                class="group animate-fade-in-up-blur block overflow-hidden rounded-[1.75rem] bg-white ring-1 ring-black/5 transition-all duration-500 [transition-timing-function:cubic-bezier(0.32,0.72,0,1)] hover:-translate-y-1 hover:shadow-[0_20px_40px_-15px_rgba(0,0,0,0.12)]"
                style="animation-delay: {{ min($loop->index, 6) * 70 }}ms"
            >
                <div class="relative aspect-video w-full overflow-hidden">
                    @if ($campaign->cover_image)
                        <img
                            src="{{ Illuminate\Support\Facades\Storage::url($campaign->cover_image) }}"
                            alt="{{ $campaign->name }}"
                            class="h-full w-full object-cover transition-transform duration-700 [transition-timing-function:cubic-bezier(0.32,0.72,0,1)] group-hover:scale-105"
                        >
                    @else
                        <div class="flex h-full w-full items-center justify-center bg-gradient-to-br from-mint-wash to-sky-wash">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-canopy-green/40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78Z"/></svg>
                        </div>
                    @endif
                </div>

                <div class="p-5">
                    <div class="flex items-center gap-1.5 text-xs text-graphite">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0 text-canopy-green" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2 9.5 4.5 6 4l-.5 3.5L2 9l2 3-2 3 3.5 1.5L6 20l3.5-.5L12 22l2.5-2.5 3.5.5.5-3.5L22 15l-2-3 2-3-3.5-1.5L18 4l-3.5.5L12 2Z"/><path d="m9 12 2 2 4-4" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/></svg>
                        {{ $campaign->account_holder ?: 'Yayasan SIDONA' }}
                    </div>

                    <h2 class="mt-2 line-clamp-2 font-semibold leading-snug tracking-tight text-ink-black transition-colors duration-300 group-hover:text-canopy-green">{{ $campaign->name }}</h2>

                    <div class="mt-3 flex items-baseline gap-1.5 text-sm">
                        <span class="text-graphite">Terkumpul</span>
                        <span class="font-semibold text-coral-pulse">Rp {{ number_format($raised, 0, ',', '.') }}</span>
                    </div>

                    <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-black/[0.06]">
                        <div class="h-full rounded-full bg-coral-pulse transition-all duration-700 [transition-timing-function:cubic-bezier(0.32,0.72,0,1)]" style="width: {{ $percent }}%"></div>
                    </div>
                </div>
            </a>
        @empty
            <p class="text-graphite sm:col-span-2 lg:col-span-3">Belum ada program donasi aktif.</p>
        @endforelse
    </div>

    <div class="mt-10">{{ $campaigns->links() }}</div>
</div>
