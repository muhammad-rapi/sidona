<div>
    <h1 class="text-xl font-semibold mb-6">Program Donasi Aktif</h1>

    <div class="grid gap-4">
        @php($pastels = ['bg-mint-wash', 'bg-sage-wash', 'bg-sky-wash', 'bg-cream', 'bg-lilac-wash', 'bg-peach-wash'])
        @forelse ($campaigns as $campaign)
            <a href="{{ route('program.show', $campaign) }}" wire:navigate class="block {{ $pastels[$loop->index % count($pastels)] }} p-8 rounded-3xl">
                <h2 class="font-bold text-lg text-canopy-green">{{ $campaign->name }}</h2>
                <p class="text-sm text-graphite mt-1">{{ $campaign->description }}</p>
                <p class="text-sm mt-2">Target Rp {{ number_format($campaign->target_amount, 0, ',', '.') }}</p>
            </a>
        @empty
            <p class="text-graphite">Belum ada program donasi aktif.</p>
        @endforelse
    </div>

    <div class="mt-4">{{ $campaigns->links() }}</div>
</div>
