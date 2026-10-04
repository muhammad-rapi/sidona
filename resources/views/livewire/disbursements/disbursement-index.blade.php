<div x-data="{ confirmId: null, confirmProgram: '' }">
    <div class="page-head">
        <div>
            <h1 class="page-title">Penyaluran Dana</h1>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            @can('create', App\Models\Disbursement::class)
                <div x-data="{ open: false }" @click.outside="open = false" @keydown.escape="open = false" class="relative">
                    <button type="button" @click="open = !open" :aria-expanded="open" class="btn btn-paint"><x-icon name="send" />Ajukan penyaluran</button>
                    <div x-show="open" x-cloak class="absolute right-0 z-20 mt-2 w-72 border-2 border-ink bg-paper">
                        @forelse ($eligibleCampaigns as $campaign)
                            <a href="{{ route('disbursements.create', $campaign) }}" wire:navigate class="block border-b border-rule px-4 py-3 last:border-b-0 hover:bg-board-wash">
                                <span class="block font-bold">{{ $campaign->name }}</span>
                                <span class="block text-xs text-ink-soft">Saldo Rp&nbsp;{{ number_format($campaign->availableBalance(), 0, ',', '.') }}</span>
                            </a>
                        @empty
                            <p class="px-4 py-3 text-sm text-ink-soft">Belum ada program aktif dengan saldo tersedia.</p>
                        @endforelse
                    </div>
                </div>
            @endcan
            <select wire:model.live="status" class="field field-sm w-auto" aria-label="Filter status">
                <option value="submitted">Diajukan</option>
                <option value="approved">Disetujui</option>
                <option value="rejected">Ditolak</option>
                <option value="all">Semua</option>
            </select>
        </div>
    </div>

    <div x-show="confirmId !== null" x-cloak class="panel mb-4 border-2 border-ink p-4">
        <h2 class="font-bold text-ink">Setujui Penyaluran?</h2>
        <p class="mt-1 text-sm text-ink-soft">
            Penyaluran untuk <span class="font-bold" x-text="confirmProgram"></span> akan ditandai sebagai disetujui.
        </p>
        <div class="mt-3 flex flex-wrap gap-2">
            <button type="button" @click="$wire.approve(confirmId); confirmId = null" class="btn btn-paint">Ya, Setujui</button>
            <button type="button" @click="confirmId = null" class="btn btn-line">Batal</button>
        </div>
    </div>

    @error('approve') <p class="error-text mb-4">{{ $message }}</p> @enderror

    <div class="panel">
        <div class="overflow-x-auto">
            <table class="ledger">
                <thead>
                    <tr>
                        <th>Program</th>
                        <th class="num">Jumlah</th>
                        <th>Keterangan</th>
                        <th>Diajukan Oleh</th>
                        <th>Status</th>
                        <th class="sticky-col">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($disbursements as $disbursement)
                        @php
                            $statusBadge = match ($disbursement->status->value) {
                                'approved' => 'badge-paid',
                                'rejected' => 'badge-fail',
                                default => 'badge-wait',
                            };
                        @endphp
                        <tr>
                            <td class="font-bold">
                                {{ $disbursement->campaign->name }}
                                @if ($disbursement->campaign->bank_name && $disbursement->campaign->account_number)
                                    <p class="text-xs font-normal text-ink-soft">{{ $disbursement->campaign->bank_name }} <span class="font-mono">{{ $disbursement->campaign->account_number }}</span></p>
                                @endif
                            </td>
                            <td class="num whitespace-nowrap">Rp&nbsp;{{ number_format($disbursement->amount, 0, ',', '.') }}</td>
                            <td>{{ $disbursement->description }}</td>
                            <td class="whitespace-nowrap">{{ $disbursement->submitter->name }}</td>
                            <td class="whitespace-nowrap">
                                <span class="badge {{ $statusBadge }}">{{ $disbursement->status->label() }}</span>
                            </td>
                            <td class="sticky-col whitespace-nowrap">
                                <div class="flex flex-nowrap items-center gap-1.5">
                                <x-action :icon="$detailId === $disbursement->id ? 'close' : 'detail'" :label="$detailId === $disbursement->id ? 'Tutup detail' : 'Lihat detail'" wire:click="toggleDetail({{ $disbursement->id }})" aria-expanded="{{ $detailId === $disbursement->id ? 'true' : 'false' }}" />
                                @can('approve', $disbursement)
                                    <x-action icon="approve" variant="ink" label="Setujui penyaluran" @click="confirmId = {{ $disbursement->id }}; confirmProgram = '{{ addslashes($disbursement->campaign->name) }}'" />
                                @endcan
                                @can('reject', $disbursement)
                                    <x-action icon="reject" variant="danger" label="Tolak penyaluran" wire:click="startReject({{ $disbursement->id }})" />
                                @endcan
                                </div>
                                @cannot('approve', $disbursement)
                                    @if ($disbursement->status === \App\Enums\DisbursementStatus::Submitted)
                                        <p class="max-w-[13rem] whitespace-normal text-sm text-ink-soft">
                                            @if (auth()->user()->hasAdminPowers())
                                                Anda yang mengajukan. Perlu admin lain untuk memutuskan.
                                            @else
                                                Menunggu keputusan admin.
                                            @endif
                                        </p>
                                    @else
                                        <p class="max-w-[13rem] whitespace-normal text-sm text-ink-soft">
                                            {{ $disbursement->status === \App\Enums\DisbursementStatus::Approved ? 'Disetujui' : 'Ditolak' }}
                                            {{ $disbursement->reviewer?->name ? 'oleh '.$disbursement->reviewer->name : '' }}
                                            @if ($disbursement->reviewed_at)
                                                <span class="block">{{ $disbursement->reviewed_at->timezone('Asia/Jakarta')->format('d/m/Y H:i') }}</span>
                                            @endif
                                        </p>
                                    @endif
                                @endcannot
                            </td>
                        </tr>
                        @if ($detailId === $disbursement->id)
                            @php $campaign = $disbursement->campaign; @endphp
                            <tr>
                                <td colspan="6" class="!bg-desk !p-0">
                                    <div class="grid gap-x-10 gap-y-6 px-5 py-5 md:grid-cols-2">
                                        <dl class="space-y-2 text-sm">
                                            <div class="flex gap-4"><dt class="w-36 shrink-0 font-bold text-ink-soft">Program</dt><dd>{{ $campaign->name }}</dd></div>
                                            <div class="flex gap-4"><dt class="w-36 shrink-0 font-bold text-ink-soft">Jumlah</dt><dd class="font-bold">Rp&nbsp;{{ number_format($disbursement->amount, 0, ',', '.') }}</dd></div>
                                            <div class="flex gap-4"><dt class="w-36 shrink-0 font-bold text-ink-soft">Peruntukan</dt><dd class="whitespace-pre-line">{{ $disbursement->description }}</dd></div>
                                            <div class="flex gap-4"><dt class="w-36 shrink-0 font-bold text-ink-soft">Rekening program</dt><dd>{{ $campaign->bank_name }} <span class="font-mono">{{ $campaign->account_number }}</span> a.n. {{ $campaign->account_holder }}</dd></div>
                                            <div class="flex gap-4"><dt class="w-36 shrink-0 font-bold text-ink-soft">Dana terkumpul</dt><dd>Rp&nbsp;{{ number_format($campaign->verifiedDonationsTotal(), 0, ',', '.') }}</dd></div>
                                            <div class="flex gap-4"><dt class="w-36 shrink-0 font-bold text-ink-soft">Sudah disalurkan</dt><dd>Rp&nbsp;{{ number_format($campaign->approvedDisbursementsTotal(), 0, ',', '.') }}</dd></div>
                                            <div class="flex gap-4"><dt class="w-36 shrink-0 font-bold text-ink-soft">Saldo tersedia</dt><dd class="font-bold">Rp&nbsp;{{ number_format($campaign->availableBalance(), 0, ',', '.') }}</dd></div>
                                            <div class="flex gap-4"><dt class="w-36 shrink-0 font-bold text-ink-soft">Diajukan</dt><dd>{{ $disbursement->submitter->name }}, {{ $disbursement->created_at->timezone('Asia/Jakarta')->format('d/m/Y H:i') }} WIB</dd></div>
                                            @if ($disbursement->reviewed_at)
                                                <div class="flex gap-4"><dt class="w-36 shrink-0 font-bold text-ink-soft">Diputuskan</dt><dd>{{ $disbursement->reviewer?->name ?? '-' }}, {{ $disbursement->reviewed_at->timezone('Asia/Jakarta')->format('d/m/Y H:i') }} WIB</dd></div>
                                            @endif
                                            @if ($disbursement->rejection_reason)
                                                <div class="flex gap-4"><dt class="w-36 shrink-0 font-bold text-ink-soft">Alasan ditolak</dt><dd class="text-paint-dark">{{ $disbursement->rejection_reason }}</dd></div>
                                            @endif
                                        </dl>

                                        <div>
                                            <p class="mb-2 text-sm font-bold text-ink-soft">Jejak audit penyaluran ini</p>
                                            @forelse ($trail as $entry)
                                                <div class="flex items-baseline justify-between gap-4 border-b border-rule py-1.5 text-sm">
                                                    <span><span class="font-mono text-xs">{{ $entry->action }}</span> oleh {{ $entry->user?->name ?? 'Sistem' }}</span>
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
                        @if ($rejectingId === $disbursement->id)
                            <tr>
                                <td colspan="6" class="bg-board-wash">
                                    <form wire:submit="confirmReject" class="flex flex-wrap items-start gap-2">
                                        <div class="min-w-[16rem] flex-1">
                                            <textarea wire:model="rejectionReason" class="field @error('rejectionReason') field-error @enderror" placeholder="Alasan penolakan"></textarea>
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
                            <td colspan="6" class="py-6 text-center text-ink-soft">Tidak ada penyaluran.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $disbursements->links() }}</div>
</div>
