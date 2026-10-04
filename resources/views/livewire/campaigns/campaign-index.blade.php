<div x-data="{ confirmId: null, confirmName: '' }">
    <div class="page-head">
        <div>
            <h1 class="page-title">Program Donasi</h1>
        </div>
        <div>
            @can('create', App\Models\Campaign::class)
                <a href="{{ route('campaigns.create') }}" wire:navigate class="btn btn-paint">Tambah Program</a>
            @endcan
        </div>
    </div>

    <div x-show="confirmId !== null" x-cloak class="panel mb-4 border-2 border-paint p-4">
        <h2 class="font-bold text-ink">Hapus Program Donasi?</h2>
        <p class="mt-1 text-sm text-ink-soft">
            <span class="font-bold" x-text="confirmName"></span> akan dihapus permanen. Tindakan ini tidak dapat dibatalkan.
        </p>
        <div class="mt-3 flex flex-wrap gap-2">
            <button type="button" @click="$wire.delete(confirmId); confirmId = null" class="btn btn-paint">Ya, Hapus</button>
            <button type="button" @click="confirmId = null" class="btn btn-line">Batal</button>
        </div>
    </div>

    <div class="panel">
        <div class="overflow-x-auto">
            <table class="ledger">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th class="num">Target</th>
                        <th>Rekening Tujuan</th>
                        <th>Periode</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($campaigns as $campaign)
                        @php
                            $statusBadge = match ($campaign->status->value) {
                                'active' => 'badge-paid',
                                'completed' => 'badge-ink',
                                default => 'badge-wait',
                            };
                        @endphp
                        <tr>
                            <td class="font-bold">{{ $campaign->name }}</td>
                            <td class="num whitespace-nowrap">Rp&nbsp;{{ number_format($campaign->target_amount, 0, ',', '.') }}</td>
                            <td class="whitespace-nowrap">
                                @if ($campaign->bank_name && $campaign->account_number)
                                    <span class="text-ink-soft">{{ $campaign->bank_name }}</span>
                                    <span class="font-mono">{{ $campaign->account_number }}</span>
                                @else
                                    <span class="text-xs font-bold text-paint-dark">Belum diisi</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap text-ink-soft">{{ $campaign->starts_on->format('d/m/Y') }} &ndash; {{ $campaign->ends_on->format('d/m/Y') }}</td>
                            <td class="whitespace-nowrap">
                                <span class="badge {{ $statusBadge }}">{{ $campaign->status->label() }}</span>
                            </td>
                            <td class="whitespace-nowrap">
                                <div class="flex flex-wrap gap-2">
                                    @can('update', $campaign)
                                        <a href="{{ route('campaigns.edit', $campaign) }}" wire:navigate class="btn btn-sm btn-line">Ubah</a>
                                    @endcan
                                    @can('create', App\Models\Disbursement::class)
                                        <a href="{{ route('disbursements.create', $campaign) }}" wire:navigate class="btn btn-sm btn-ink">Ajukan Penyaluran</a>
                                    @endcan
                                    @can('delete', $campaign)
                                        <button type="button" @click="confirmId = {{ $campaign->id }}; confirmName = '{{ addslashes($campaign->name) }}'" class="btn btn-sm btn-line text-paint-dark">Hapus</button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-6 text-center text-ink-soft">Belum ada program donasi.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $campaigns->links() }}</div>
</div>
