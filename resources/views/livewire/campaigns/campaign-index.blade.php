<div>
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-xl font-semibold">Program Donasi</h1>
        @can('create', App\Models\Campaign::class)
            <a href="{{ route('campaigns.create') }}" wire:navigate class="rounded-full bg-coral-pulse px-6 py-3 text-white text-sm font-medium transition-all duration-200 ease-out hover:scale-[1.03] hover:bg-coral-pulse-dark active:scale-95">Tambah Program</a>
        @endcan
    </div>

    <div class="overflow-x-auto">
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
                    <td class="border-b border-frost-gray px-4 py-3 font-mono whitespace-nowrap">Rp {{ number_format($campaign->target_amount, 0, ',', '.') }}</td>
                    <td class="border-b border-frost-gray px-4 py-3 whitespace-nowrap">{{ $campaign->starts_on->format('d/m/Y') }} sampai {{ $campaign->ends_on->format('d/m/Y') }}</td>
                    <td class="border-b border-frost-gray px-4 py-3 whitespace-nowrap">{{ $campaign->status->label() }}</td>
                    <td class="border-b border-frost-gray px-4 py-3 whitespace-nowrap space-x-3">
                        @can('update', $campaign)
                            <a href="{{ route('campaigns.edit', $campaign) }}" wire:navigate class="text-coral-pulse hover:text-coral-pulse-dark transition-colors duration-200">Ubah</a>
                        @endcan
                        @can('delete', $campaign)
                            <button type="button" wire:click="delete({{ $campaign->id }})" wire:confirm="Yakin ingin menghapus program ini?" class="text-flag-red hover:text-flag-red/70 transition-colors duration-200">Hapus</button>
                        @endcan
                        @can('create', App\Models\Disbursement::class)
                            <a href="{{ route('disbursements.create', $campaign) }}" wire:navigate class="text-canopy-green hover:text-leaf-bright transition-colors duration-200">Ajukan Penyaluran</a>
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
    </div>

    <div class="mt-4">{{ $campaigns->links() }}</div>
</div>
