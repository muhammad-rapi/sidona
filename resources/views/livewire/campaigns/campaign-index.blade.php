<div>
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-xl font-semibold">Program Donasi</h1>
        @can('create', App\Models\Campaign::class)
            <a href="{{ route('campaigns.create') }}" wire:navigate class="rounded bg-slate-900 px-4 py-2 text-white text-sm">Tambah Program</a>
        @endcan
    </div>

    <table class="w-full border border-slate-300 text-sm bg-white">
        <thead class="bg-slate-200">
            <tr>
                <th class="border border-slate-300 px-3 py-2 text-left">Nama</th>
                <th class="border border-slate-300 px-3 py-2 text-left">Target</th>
                <th class="border border-slate-300 px-3 py-2 text-left">Periode</th>
                <th class="border border-slate-300 px-3 py-2 text-left">Status</th>
                <th class="border border-slate-300 px-3 py-2 text-left">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($campaigns as $campaign)
                <tr>
                    <td class="border border-slate-300 px-3 py-2">{{ $campaign->name }}</td>
                    <td class="border border-slate-300 px-3 py-2">Rp {{ number_format($campaign->target_amount, 0, ',', '.') }}</td>
                    <td class="border border-slate-300 px-3 py-2">{{ $campaign->starts_on->format('d/m/Y') }} sampai {{ $campaign->ends_on->format('d/m/Y') }}</td>
                    <td class="border border-slate-300 px-3 py-2">{{ $campaign->status->label() }}</td>
                    <td class="border border-slate-300 px-3 py-2 space-x-2">
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
                    <td colspan="5" class="border border-slate-300 px-3 py-6 text-center text-slate-500">Belum ada program donasi.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="mt-4">{{ $campaigns->links() }}</div>
</div>
