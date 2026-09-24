<div>
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-xl font-semibold">Program Donasi</h1>
        @can('create', App\Models\Campaign::class)
            <a href="{{ route('campaigns.create') }}" wire:navigate class="rounded-full bg-coral-pulse px-6 py-3 text-white text-sm font-medium transition-colors duration-200 hover:bg-coral-pulse-dark">Tambah Program</a>
        @endcan
    </div>

    <table class="w-full text-sm">
        <thead>
            <tr>
                <th class="border-b-2 border-ink-black px-4 py-3 text-left font-medium text-graphite">Nama</th>
                <th class="border-b-2 border-ink-black px-4 py-3 text-left font-medium text-graphite">Target</th>
                <th class="border-b-2 border-ink-black px-4 py-3 text-left font-medium text-graphite">Periode</th>
                <th class="border-b-2 border-ink-black px-4 py-3 text-left font-medium text-graphite">Status</th>
                <th class="border-b-2 border-ink-black px-4 py-3 text-left font-medium text-graphite">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($campaigns as $campaign)
                <tr>
                    <td class="border-b border-frost-gray px-4 py-3">{{ $campaign->name }}</td>
                    <td class="border-b border-frost-gray px-4 py-3 font-mono">Rp {{ number_format($campaign->target_amount, 0, ',', '.') }}</td>
                    <td class="border-b border-frost-gray px-4 py-3">{{ $campaign->starts_on->format('d/m/Y') }} sampai {{ $campaign->ends_on->format('d/m/Y') }}</td>
                    <td class="border-b border-frost-gray px-4 py-3">{{ $campaign->status->label() }}</td>
                    <td class="border-b border-frost-gray px-4 py-3 space-x-2">
                        @can('update', $campaign)
                            <a href="{{ route('campaigns.edit', $campaign) }}" wire:navigate>Ubah</a>
                        @endcan
                        @can('delete', $campaign)
                            <button type="button" wire:click="delete({{ $campaign->id }})" wire:confirm="Yakin ingin menghapus program ini?">Hapus</button>
                        @endcan
                        @can('create', App\Models\Disbursement::class)
                            <a href="{{ route('disbursements.create', $campaign) }}" wire:navigate>Ajukan Penyaluran</a>
                        @endcan
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="border-b border-frost-gray px-4 py-6 text-center text-graphite">Belum ada program donasi.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="mt-4">{{ $campaigns->links() }}</div>
</div>
