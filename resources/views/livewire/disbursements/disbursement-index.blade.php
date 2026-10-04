<div x-data="{ confirmId: null, confirmProgram: '' }">
    <div class="page-head">
        <div>
            <h1 class="page-title">Penyaluran Dana</h1>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            @can('create', App\Models\Disbursement::class)
                <div x-data="{ open: false }" @click.outside="open = false" @keydown.escape="open = false" class="relative">
                    <button type="button" @click="open = !open" :aria-expanded="open" class="btn btn-paint">Ajukan penyaluran</button>
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
                        <th>Aksi</th>
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
                            <td class="whitespace-nowrap">
                                <div class="flex flex-wrap gap-2">
                                @can('approve', $disbursement)
                                    <button type="button" @click="confirmId = {{ $disbursement->id }}; confirmProgram = '{{ addslashes($disbursement->campaign->name) }}'" class="btn btn-sm btn-ink">Setujui</button>
                                @endcan
                                @can('reject', $disbursement)
                                    <button type="button" wire:click="startReject({{ $disbursement->id }})" class="btn btn-sm btn-line text-paint-dark">Tolak</button>
                                @endcan
                                </div>
                            </td>
                        </tr>
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
