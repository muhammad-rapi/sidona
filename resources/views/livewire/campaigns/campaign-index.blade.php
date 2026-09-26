<div>
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-xl font-semibold">Program Donasi</h1>
        @can('create', App\Models\Campaign::class)
            <a href="{{ route('campaigns.create') }}" wire:navigate class="rounded-full bg-coral-pulse px-6 py-3 text-white text-sm font-medium transition-all duration-200 ease-out hover:scale-[1.03] hover:bg-coral-pulse-dark active:scale-95">Tambah Program</a>
        @endcan
    </div>

    <div class="bg-white rounded-3xl border border-frost-gray overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr>
                        <th class="border-b-2 border-ink-black px-4 py-3 text-left font-medium text-graphite">Nama</th>
                        <th class="border-b-2 border-ink-black px-4 py-3 text-left font-medium text-graphite">Target</th>
                        <th class="border-b-2 border-ink-black px-4 py-3 text-left font-medium text-graphite">Rekening Tujuan</th>
                        <th class="border-b-2 border-ink-black px-4 py-3 text-left font-medium text-graphite">Periode</th>
                        <th class="border-b-2 border-ink-black px-4 py-3 text-left font-medium text-graphite">Status</th>
                        <th class="border-b-2 border-ink-black px-4 py-3 text-left font-medium text-graphite">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($campaigns as $campaign)
                        @php
                            $statusBadge = match ($campaign->status->value) {
                                'active' => ['bg-mint-wash', 'text-canopy-green'],
                                'completed' => ['bg-sky-wash', 'text-canopy-green'],
                                default => ['bg-sand', 'text-graphite'],
                            };
                        @endphp
                        <tr class="hover:bg-mint-wash/40 transition-colors duration-150">
                            <td class="border-b border-frost-gray px-4 py-3 font-medium">{{ $campaign->name }}</td>
                            <td class="border-b border-frost-gray px-4 py-3 font-mono whitespace-nowrap">Rp {{ number_format($campaign->target_amount, 0, ',', '.') }}</td>
                            <td class="border-b border-frost-gray px-4 py-3 whitespace-nowrap">
                                @if ($campaign->bank_name && $campaign->account_number)
                                    <span class="text-graphite">{{ $campaign->bank_name }}</span>
                                    <span class="font-mono">{{ $campaign->account_number }}</span>
                                @else
                                    <span class="text-flag-red text-xs">Belum diisi</span>
                                @endif
                            </td>
                            <td class="border-b border-frost-gray px-4 py-3 whitespace-nowrap text-graphite">{{ $campaign->starts_on->format('d/m/Y') }} &ndash; {{ $campaign->ends_on->format('d/m/Y') }}</td>
                            <td class="border-b border-frost-gray px-4 py-3 whitespace-nowrap">
                                <span class="inline-flex items-center rounded-full {{ $statusBadge[0] }} {{ $statusBadge[1] }} px-3 py-1 text-xs font-medium">{{ $campaign->status->label() }}</span>
                            </td>
                            <td class="border-b border-frost-gray px-4 py-3 whitespace-nowrap space-x-2">
                                @can('update', $campaign)
                                    <a href="{{ route('campaigns.edit', $campaign) }}" wire:navigate class="inline-flex items-center rounded-full border border-frost-gray px-3 py-1 text-xs font-medium text-graphite transition-colors duration-200 hover:border-coral-pulse hover:text-coral-pulse">Ubah</a>
                                @endcan
                                @can('create', App\Models\Disbursement::class)
                                    <a href="{{ route('disbursements.create', $campaign) }}" wire:navigate class="inline-flex items-center rounded-full bg-mint-wash px-3 py-1 text-xs font-medium text-canopy-green transition-colors duration-200 hover:bg-sky-wash">Ajukan Penyaluran</a>
                                @endcan
                                @can('delete', $campaign)
                                    <button type="button" wire:click="delete({{ $campaign->id }})" wire:confirm="Yakin ingin menghapus program ini?" class="inline-flex items-center rounded-full border border-flag-red/30 px-3 py-1 text-xs font-medium text-flag-red transition-colors duration-200 hover:bg-flag-red/10">Hapus</button>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="border-b border-frost-gray px-4 py-6 text-center text-graphite">Belum ada program donasi.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $campaigns->links() }}</div>
</div>
