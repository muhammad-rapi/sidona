<div>
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-xl font-semibold">Penyaluran Dana</h1>
        <select wire:model.live="status" class="rounded border border-line px-3 py-2 text-sm">
            <option value="submitted">Diajukan</option>
            <option value="approved">Disetujui</option>
            <option value="rejected">Ditolak</option>
            <option value="all">Semua</option>
        </select>
    </div>

    @error('approve') <p class="text-sm text-red-700 mb-4">{{ $message }}</p> @enderror

    <table class="w-full text-sm">
        <thead>
            <tr>
                <th class="border-b-2 border-ink px-4 py-3 text-left font-medium text-ink-muted">Program</th>
                <th class="border-b-2 border-ink px-4 py-3 text-left font-medium text-ink-muted">Jumlah</th>
                <th class="border-b-2 border-ink px-4 py-3 text-left font-medium text-ink-muted">Keterangan</th>
                <th class="border-b-2 border-ink px-4 py-3 text-left font-medium text-ink-muted">Diajukan Oleh</th>
                <th class="border-b-2 border-ink px-4 py-3 text-left font-medium text-ink-muted">Status</th>
                <th class="border-b-2 border-ink px-4 py-3 text-left font-medium text-ink-muted">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($disbursements as $disbursement)
                <tr>
                    <td class="border-b border-line px-4 py-3">{{ $disbursement->campaign->name }}</td>
                    <td class="border-b border-line px-4 py-3 font-mono">Rp {{ number_format($disbursement->amount, 0, ',', '.') }}</td>
                    <td class="border-b border-line px-4 py-3">{{ $disbursement->description }}</td>
                    <td class="border-b border-line px-4 py-3">{{ $disbursement->submitter->name }}</td>
                    <td class="border-b border-line px-4 py-3">
                        @php($statusColor = match ($disbursement->status->value) { 'approved' => 'text-verified', 'rejected' => 'text-flagged', default => 'text-ink-muted' })
                        <span class="{{ $statusColor }}">&bull; {{ $disbursement->status->label() }}</span>
                    </td>
                    <td class="border-b border-line px-4 py-3 space-x-2">
                        @can('approve', $disbursement)
                            <button type="button" wire:click="approve({{ $disbursement->id }})" wire:confirm="Setujui penyaluran ini?">Setujui</button>
                        @endcan
                        @can('reject', $disbursement)
                            <button type="button" wire:click="startReject({{ $disbursement->id }})">Tolak</button>
                        @endcan
                    </td>
                </tr>
                @if ($rejectingId === $disbursement->id)
                    <tr>
                        <td colspan="6" class="border-b border-line px-4 py-3 bg-paper">
                            <form wire:submit="confirmReject" class="flex items-start gap-3">
                                <div class="flex-1">
                                    <textarea wire:model="rejectionReason" class="w-full rounded border border-line px-3 py-2 text-sm" placeholder="Alasan penolakan"></textarea>
                                    @error('rejectionReason') <p class="text-sm text-red-700 mt-1">{{ $message }}</p> @enderror
                                </div>
                                <button type="submit" class="rounded-full bg-coral px-5 py-2 text-white text-sm font-medium transition-colors duration-200 hover:bg-coral-dark">Kirim</button>
                                <button type="button" wire:click="cancelReject" class="rounded-full border border-ink px-5 py-2 text-sm transition-colors duration-200 hover:bg-ink hover:text-white">Batal</button>
                            </form>
                        </td>
                    </tr>
                @endif
            @empty
                <tr>
                    <td colspan="6" class="border-b border-line px-4 py-6 text-center text-ink-muted">Tidak ada penyaluran.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="mt-4">{{ $disbursements->links() }}</div>
</div>
