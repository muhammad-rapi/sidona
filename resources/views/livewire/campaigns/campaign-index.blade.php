<div x-data="{ confirmId: null, confirmName: '' }">
    <div class="page-head">
        <div>
            <h1 class="page-title">Program Donasi</h1>
        </div>
        <div>
            @can('create', App\Models\Campaign::class)
                <a href="{{ route('campaigns.create') }}" wire:navigate class="btn btn-paint"><x-icon name="plus" />Tambah Program</a>
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
                        <th>Periode</th>
                        <th>Status</th>
                        <th class="sticky-col">Aksi</th>
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
                                @if ($campaign->picName())
                                    <p class="text-xs font-normal text-ink-soft">PIC: {{ $campaign->picName() }}</p>
                                @endif
                                @if ($campaign->proposer_name)
                                    <p class="text-xs font-normal text-ink-soft">Diajukan {{ $campaign->proposer_name }} ({{ $campaign->proposer_contact }})</p>
                                @endif
                                @if ($campaign->status === \App\Enums\CampaignStatus::Rejected && $campaign->rejection_reason)
                                    <p class="text-xs font-normal text-paint-dark">Alasan ditolak: {{ $campaign->rejection_reason }}</p>
                                @endif
                            </td>
                            <td class="num whitespace-nowrap">Rp&nbsp;{{ number_format($campaign->target_amount, 0, ',', '.') }}</td>
                            <td class="whitespace-nowrap text-ink-soft">{{ $campaign->starts_on->format('d/m/Y') }} &ndash; {{ $campaign->ends_on->format('d/m/Y') }}</td>
                            <td class="whitespace-nowrap">
                                <span class="badge {{ $statusBadge }}">{{ $campaign->status->label() }}</span>
                            </td>
                            <td class="sticky-col whitespace-nowrap">
                                <div class="flex flex-nowrap items-center gap-1.5">
                                    <x-action :icon="$detailId === $campaign->id ? 'close' : 'detail'" :label="$detailId === $campaign->id ? 'Tutup detail' : 'Lihat detail'" wire:click="toggleDetail({{ $campaign->id }})" aria-expanded="{{ $detailId === $campaign->id ? 'true' : 'false' }}" />
                                    @if ($campaign->status === \App\Enums\CampaignStatus::Active)
                                        <x-action icon="view" label="Buka halaman publik" :href="route('program.show', $campaign)" target="_blank" rel="noopener" />
                                    @else
                                        @cannot('update', $campaign)
                                            
                                        @endcannot
                                    @endif
                                    @can('review', $campaign)
                                        <x-action icon="approve" variant="ink" label="Setujui dan tayangkan" wire:click="approve({{ $campaign->id }})" wire:confirm="Setujui dan tayangkan program ini?" />
                                        <x-action icon="reject" variant="danger" label="Tolak pengajuan" wire:click="startReject({{ $campaign->id }})" />
                                    @endcan
                                    @can('update', $campaign)
                                        <x-action icon="edit" label="Ubah program" :href="route('campaigns.edit', $campaign)" wire:navigate />
                                    @endcan
                                    @if ($campaign->status === \App\Enums\CampaignStatus::Active)
                                    @can('create', App\Models\Disbursement::class)
                                        <x-action icon="send" variant="ink" label="Ajukan penyaluran dana" :href="route('disbursements.create', $campaign)" wire:navigate />
                                    @endcan
                                    @endif
                                    @can('delete', $campaign)
                                        <x-action icon="trash" variant="danger" label="Hapus program" @click="confirmId = {{ $campaign->id }}; confirmName = '{{ addslashes($campaign->name) }}'" />
                                    @endcan
                                </div>
                            </td>
                        </tr>
                        @if ($detailId === $campaign->id)
                            @php
                                $raised = $campaign->verifiedDonationsTotal();
                                $donorCount = $campaign->donations()->where('status', \App\Enums\DonationStatus::Verified)->count();
                            @endphp
                            <tr>
                                <td colspan="5" class="!bg-desk !p-0">
                                    <div class="grid gap-x-10 gap-y-6 px-5 py-5 md:grid-cols-2">
                                        <dl class="space-y-2 text-sm">
                                            <div class="flex gap-4"><dt class="w-36 shrink-0 font-bold text-ink-soft">Deskripsi</dt><dd class="whitespace-pre-line">{{ $campaign->description ?: '-' }}</dd></div>
                                            <div class="flex gap-4"><dt class="w-36 shrink-0 font-bold text-ink-soft">Penanggung jawab</dt><dd>{{ $campaign->picName() ?: '-' }}@if ($campaign->picContact()) <span class="text-ink-soft">({{ $campaign->picContact() }})</span>@endif</dd></div>
                                            <div class="flex gap-4"><dt class="w-36 shrink-0 font-bold text-ink-soft">Rekening tujuan</dt><dd>@if ($campaign->bank_name){{ $campaign->bank_name }} <span class="font-mono">{{ $campaign->account_number }}</span> a.n. {{ $campaign->account_holder }}@else<span class="font-bold text-paint-dark">Belum diisi</span>@endif</dd></div>
                                            <div class="flex gap-4"><dt class="w-36 shrink-0 font-bold text-ink-soft">Target</dt><dd>Rp&nbsp;{{ number_format($campaign->target_amount, 0, ',', '.') }}</dd></div>
                                            <div class="flex gap-4"><dt class="w-36 shrink-0 font-bold text-ink-soft">Terkumpul</dt><dd>Rp&nbsp;{{ number_format($raised, 0, ',', '.') }} ({{ $campaign->progressPercent($raised) }}%) dari {{ $donorCount }} donasi</dd></div>
                                            <div class="flex gap-4"><dt class="w-36 shrink-0 font-bold text-ink-soft">Sudah disalurkan</dt><dd>Rp&nbsp;{{ number_format($campaign->approvedDisbursementsTotal(), 0, ',', '.') }}</dd></div>
                                            <div class="flex gap-4"><dt class="w-36 shrink-0 font-bold text-ink-soft">Saldo tersedia</dt><dd class="font-bold">Rp&nbsp;{{ number_format($campaign->availableBalance(), 0, ',', '.') }}</dd></div>
                                            <div class="flex gap-4"><dt class="w-36 shrink-0 font-bold text-ink-soft">Foto</dt><dd>{{ $campaign->cover_image ? 'Ada sampul' : 'Tanpa sampul' }}, {{ $campaign->photos()->count() }} foto galeri</dd></div>
                                            @if ($campaign->proposer_name)
                                                <div class="flex gap-4"><dt class="w-36 shrink-0 font-bold text-ink-soft">Pengaju</dt><dd>{{ $campaign->proposer_name }}, {{ $campaign->proposer_contact }}</dd></div>
                                            @endif
                                            @if ($campaign->reviewed_at)
                                                <div class="flex gap-4"><dt class="w-36 shrink-0 font-bold text-ink-soft">Ditinjau</dt><dd>{{ $campaign->reviewed_at->timezone('Asia/Jakarta')->format('d/m/Y H:i') }} WIB</dd></div>
                                            @endif
                                            @if ($campaign->rejection_reason)
                                                <div class="flex gap-4"><dt class="w-36 shrink-0 font-bold text-ink-soft">Alasan ditolak</dt><dd class="text-paint-dark">{{ $campaign->rejection_reason }}</dd></div>
                                            @endif
                                        </dl>

                                        <div>
                                            <p class="mb-2 text-sm font-bold text-ink-soft">Jejak audit program ini</p>
                                            @forelse ($trail as $entry)
                                                <div class="flex items-baseline justify-between gap-4 border-b border-rule py-1.5 text-sm">
                                                    <span><span class="font-mono text-xs">{{ $entry->action }}</span> oleh {{ $entry->user?->name ?? 'Tamu' }}</span>
                                                    <span class="shrink-0 text-ink-soft">{{ $entry->created_at->timezone('Asia/Jakarta')->format('d/m/Y H:i') }}</span>
                                                </div>
                                            @empty
                                                <p class="text-sm text-ink-soft">Belum ada catatan audit.</p>
                                            @endforelse
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endif
                        @if ($rejectingId === $campaign->id)
                            <tr>
                                <td colspan="5" class="bg-board-wash">
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
                            <td colspan="5" class="py-6 text-center text-ink-soft">Belum ada program donasi.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $campaigns->links() }}</div>
</div>
