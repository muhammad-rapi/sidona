<div>
    <h1 class="text-xl font-semibold mb-6">Program Donasi Aktif</h1>

    <div class="grid gap-4">
        @forelse ($campaigns as $campaign)
            <a href="{{ route('program.show', $campaign) }}" wire:navigate class="block bg-white p-5 rounded border border-slate-300 hover:border-slate-900">
                <h2 class="font-semibold">{{ $campaign->name }}</h2>
                <p class="text-sm text-slate-600 mt-1">{{ $campaign->description }}</p>
                <p class="text-sm mt-2">Target Rp {{ number_format($campaign->target_amount, 0, ',', '.') }}</p>
            </a>
        @empty
            <p class="text-slate-500">Belum ada program donasi aktif.</p>
        @endforelse
    </div>

    <div class="mt-4">{{ $campaigns->links() }}</div>
</div>
