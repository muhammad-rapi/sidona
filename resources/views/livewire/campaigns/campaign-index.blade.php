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
                                'rejected' => 'badge-fail',
                                default => 'badge-wait',
                            };
                        @endphp
                        <tr>
                            <td class="font-bold">
                                {{ $campaign->name }}
                                @if ($campaign->proposer_name)
                                    <p class="text-xs font-normal text-ink-soft">Diajukan {{ $campaign->proposer_name }} ({{ $campaign->proposer_contact }})</p>
                                @endif
                                @if ($campaign->status === \App\Enums\CampaignStatus::Rejected && $campaign->rejection_reason)
                                    <p class="text-xs font-normal text-paint-dark">Alasan ditolak: {{ $campaign->rejection_reason }}</p>
                                @endif
                            </td>
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
                                    @can('review', $campaign)
                                        <button type="button" wire:click="approve({{ $campaign->id }})" wire:confirm="Setujui dan tayangkan program ini?" class="btn btn-sm btn-ink">Setujui</button>
                                        <button type="button" wire:click="startReject({{ $campaign->id }})" class="btn btn-sm btn-line text-paint-dark">Tolak</button>
                                    @endcan
                                    @can('update', $campaign)
                                        <a href="{{ route('campaigns.edit', $campaign) }}" wire:navigate class="btn btn-sm btn-line">Ubah</a>
                                    @endcan
                                    @if ($campaign->status === \App\Enums\CampaignStatus::Active)
                                    @can('create', App\Models\Disbursement::class)
                                        <a href="{{ route('disbursements.create', $campaign) }}" wire:navigate class="btn btn-sm btn-ink">Ajukan Penyaluran</a>
                                    @endcan
                                    @endif
                                    @can('delete', $campaign)
                                        <button type="button" @click="confirmId = {{ $campaign->id }}; confirmName = '{{ addslashes($campaign->name) }}'" class="btn btn-sm btn-line text-paint-dark">Hapus</button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                        @if ($rejectingId === $campaign->id)
                            <tr>
                                <td colspan="6" class="bg-board-wash">
                                    <form wire:submit="confirmReject" class="flex flex-wrap items-start gap-2">
                                        <div class="min-w-[16rem] flex-1">
                                            <input type="text" wire:model="rejectionReason" class="field @error('rejectionReason') field-error @enderror" placeholder="Alasan penolakan" aria-label="Alasan penolakan">
                                            @error('rejectionReason') <p class="error-text">{{ $message }}</p> @enderror
                                        </div>
                                        <button type="submit" class="btn btn-paint">Kirim</button>
                                        <button type="button" wire:click="cancelReject" class="btn btn-line">Batal</button>
                                    </form>
                                </td>
                            </tr>
                        @endif
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
