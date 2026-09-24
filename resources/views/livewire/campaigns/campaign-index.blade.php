<div>
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-xl font-semibold">Program Donasi</h1>
        @can('create', App\Models\Campaign::class)
            <a href="{{ route('campaigns.create') }}" wire:navigate class="rounded-md bg-ink px-4 py-2 text-white text-sm">Tambah Program</a>
        @endcan
    </div>

    <table class="w-full text-sm">
        <thead>
            <tr>
                <th class="border-b-2 border-ink px-4 py-3 text-left font-medium text-ink-muted">Nama</th>
                <th class="border-b-2 border-ink px-4 py-3 text-left font-medium text-ink-muted">Target</th>
                <th class="border-b-2 border-ink px-4 py-3 text-left font-medium text-ink-muted">Periode</th>
                <th class="border-b-2 border-ink px-4 py-3 text-left font-medium text-ink-muted">Status</th>
                <th class="border-b-2 border-ink px-4 py-3 text-left font-medium text-ink-muted">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($campaigns as $campaign)
                <tr>
                    <td class="border-b border-line px-4 py-3">{{ $campaign->name }}</td>
                    <td class="border-b border-line px-4 py-3 font-mono">Rp {{ number_format($campaign->target_amount, 0, ',', '.') }}</td>
                    <td class="border-b border-line px-4 py-3">{{ $campaign->starts_on->format('d/m/Y') }} sampai {{ $campaign->ends_on->format('d/m/Y') }}</td>
                    <td class="border-b border-line px-4 py-3">{{ $campaign->status->label() }}</td>
                    <td class="border-b border-line px-4 py-3 space-x-2">
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
                    <td colspan="5" class="border-b border-line px-4 py-6 text-center text-ink-muted">Belum ada program donasi.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="mt-4">{{ $campaigns->links() }}</div>
</div>
