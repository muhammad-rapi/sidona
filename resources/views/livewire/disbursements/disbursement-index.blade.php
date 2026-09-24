<div>
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-xl font-semibold">Penyaluran Dana</h1>
        <select wire:model.live="status" class="rounded border border-frost-gray px-3 py-2 text-sm">
            <option value="submitted">Diajukan</option>
            <option value="approved">Disetujui</option>
            <option value="rejected">Ditolak</option>
            <option value="all">Semua</option>
        </select>
    </div>

    @error('approve') <p class="text-sm text-red-700 mb-4">{{ $message }}</p> @enderror

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
        <thead>
            <tr>
                <th class="border-b-2 border-ink-black px-4 py-3 text-left font-medium text-graphite">Program</th>
                <th class="border-b-2 border-ink-black px-4 py-3 text-left font-medium text-graphite">Jumlah</th>
                <th class="border-b-2 border-ink-black px-4 py-3 text-left font-medium text-graphite">Keterangan</th>
                <th class="border-b-2 border-ink-black px-4 py-3 text-left font-medium text-graphite">Diajukan Oleh</th>
                <th class="border-b-2 border-ink-black px-4 py-3 text-left font-medium text-graphite">Status</th>
                <th class="border-b-2 border-ink-black px-4 py-3 text-left font-medium text-graphite">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($disbursements as $disbursement)
                <tr>
                    <td class="border-b border-frost-gray px-4 py-3">{{ $disbursement->campaign->name }}</td>
                    <td class="border-b border-frost-gray px-4 py-3 font-mono whitespace-nowrap">Rp {{ number_format($disbursement->amount, 0, ',', '.') }}</td>
                    <td class="border-b border-frost-gray px-4 py-3">{{ $disbursement->description }}</td>
                    <td class="border-b border-frost-gray px-4 py-3">{{ $disbursement->submitter->name }}</td>
                    <td class="border-b border-frost-gray px-4 py-3">
                        @php($statusColor = match ($disbursement->status->value) { 'approved' => 'text-leaf-bright', 'rejected' => 'text-flag-red', default => 'text-graphite' })
                        <span class="{{ $statusColor }}">&bull; {{ $disbursement->status->label() }}</span>
                    </td>
                    <td class="border-b border-frost-gray px-4 py-3 whitespace-nowrap space-x-3">
                        @can('approve', $disbursement)
                            <button type="button" wire:click="approve({{ $disbursement->id }})" wire:confirm="Setujui penyaluran ini?" class="text-leaf-bright hover:text-canopy-green transition-colors duration-200">Setujui</button>
                        @endcan
                        @can('reject', $disbursement)
                            <button type="button" wire:click="startReject({{ $disbursement->id }})" class="text-flag-red hover:text-flag-red/70 transition-colors duration-200">Tolak</button>
                        @endcan
                    </td>
                </tr>
                @if ($rejectingId === $disbursement->id)
                    <tr>
                        <td colspan="6" class="border-b border-frost-gray px-4 py-3 bg-cloud-gray">
                            <form wire:submit="confirmReject" class="flex items-start gap-3">
                                <div class="flex-1">
                                    <textarea wire:model="rejectionReason" class="w-full rounded border border-frost-gray px-3 py-2 text-sm" placeholder="Alasan penolakan"></textarea>
                                    @error('rejectionReason') <p class="text-sm text-red-700 mt-1">{{ $message }}</p> @enderror
                                </div>
                                <button type="submit" class="rounded-full bg-coral-pulse px-5 py-2 text-white text-sm font-medium transition-all duration-200 ease-out hover:scale-[1.03] hover:bg-coral-pulse-dark active:scale-95">Kirim</button>
                                <button type="button" wire:click="cancelReject" class="rounded-full border border-ink-black px-5 py-2 text-sm transition-all duration-200 ease-out hover:scale-[1.03] hover:bg-ink-black hover:text-white active:scale-95">Batal</button>
                            </form>
                        </td>
                    </tr>
                @endif
            @empty
                <tr>
                    <td colspan="6" class="border-b border-frost-gray px-4 py-6 text-center text-graphite">Tidak ada penyaluran.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    </div>

    <div class="mt-4">{{ $disbursements->links() }}</div>
</div>
