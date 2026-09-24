<div>
    <div class="mb-10">
        <h1 class="text-2xl font-semibold text-ink-black">Program Donasi Aktif</h1>
        <p class="text-sm text-graphite mt-1">Pilih program yang ingin Anda bantu.</p>
    </div>

    <div class="grid gap-6 sm:grid-cols-2">
        @forelse ($campaigns as $campaign)
            @php
                $raised = $campaign->verifiedDonationsTotal();
                $percent = $campaign->target_amount > 0 ? min(100, (int) round($raised / $campaign->target_amount * 100)) : 0;
            @endphp
            <a
                href="{{ route('program.show', $campaign) }}"
                wire:navigate
                class="group block bg-white rounded-3xl border border-frost-gray p-6 transition-all duration-200 ease-out hover:-translate-y-1 hover:shadow-lg"
            >
                <h2 class="font-semibold text-lg text-ink-black transition-colors duration-200 group-hover:text-canopy-green">{{ $campaign->name }}</h2>
                <p class="text-sm text-graphite mt-1 line-clamp-2">{{ $campaign->description }}</p>

                <div class="mt-5">
                    <div class="h-2 rounded-full bg-mint-wash overflow-hidden">
                        <div class="h-full rounded-full bg-coral-pulse" style="width: {{ $percent }}%"></div>
                    </div>
                    <div class="mt-2 flex items-baseline justify-between text-sm">
                        <span class="font-medium text-canopy-green">Rp {{ number_format($raised, 0, ',', '.') }}</span>
                        <span class="text-graphite">{{ $percent }}% dari Rp {{ number_format($campaign->target_amount, 0, ',', '.') }}</span>
                    </div>
                </div>
            </a>
        @empty
            <p class="text-graphite sm:col-span-2">Belum ada program donasi aktif.</p>
        @endforelse
    </div>

    <div class="mt-8">{{ $campaigns->links() }}</div>
</div>
