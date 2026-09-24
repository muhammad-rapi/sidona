<div>
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-xl font-semibold">Penyaluran Dana</h1>
        <select wire:model.live="status" class="rounded-full border border-frost-gray px-4 py-2 text-sm outline-none transition-all duration-200 focus:border-coral-pulse focus:ring-2 focus:ring-coral-pulse/20">
            <option value="submitted">Diajukan</option>
            <option value="approved">Disetujui</option>
            <option value="rejected">Ditolak</option>
            <option value="all">Semua</option>
        </select>
    </div>

    @error('approve') <p class="text-sm text-red-700 mb-4">{{ $message }}</p> @enderror

    <div class="bg-white rounded-3xl border border-frost-gray overflow-hidden">
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
                        @php
                            $statusBadge = match ($disbursement->status->value) {
                                'approved' => ['bg-mint-wash', 'text-canopy-green'],
                                'rejected' => ['bg-flag-red/10', 'text-flag-red'],
                                default => ['bg-sand', 'text-graphite'],
                            };
                        @endphp
                        <tr class="hover:bg-mint-wash/40 transition-colors duration-150">
                            <td class="border-b border-frost-gray px-4 py-3 font-medium">{{ $disbursement->campaign->name }}</td>
                            <td class="border-b border-frost-gray px-4 py-3 font-mono whitespace-nowrap">Rp {{ number_format($disbursement->amount, 0, ',', '.') }}</td>
                            <td class="border-b border-frost-gray px-4 py-3">{{ $disbursement->description }}</td>
                            <td class="border-b border-frost-gray px-4 py-3 whitespace-nowrap">{{ $disbursement->submitter->name }}</td>
                            <td class="border-b border-frost-gray px-4 py-3 whitespace-nowrap">
                                <span class="inline-flex items-center rounded-full {{ $statusBadge[0] }} {{ $statusBadge[1] }} px-3 py-1 text-xs font-medium">{{ $disbursement->status->label() }}</span>
                            </td>
                            <td class="border-b border-frost-gray px-4 py-3 whitespace-nowrap space-x-2">
                                @can('approve', $disbursement)
                                    <button type="button" wire:click="approve({{ $disbursement->id }})" wire:confirm="Setujui penyaluran ini?" class="inline-flex items-center rounded-full bg-mint-wash px-3 py-1 text-xs font-medium text-canopy-green transition-colors duration-200 hover:bg-sky-wash">Setujui</button>
                                @endcan
                                @can('reject', $disbursement)
                                    <button type="button" wire:click="startReject({{ $disbursement->id }})" class="inline-flex items-center rounded-full border border-flag-red/30 px-3 py-1 text-xs font-medium text-flag-red transition-colors duration-200 hover:bg-flag-red/10">Tolak</button>
                                @endcan
                            </td>
                        </tr>
                        @if ($rejectingId === $disbursement->id)
                            <tr>
                                <td colspan="6" class="border-b border-frost-gray px-4 py-3 bg-cloud-gray">
                                    <form wire:submit="confirmReject" class="flex items-start gap-3">
                                        <div class="flex-1">
                                            <textarea wire:model="rejectionReason" class="w-full rounded border border-frost-gray px-3 py-2 text-sm outline-none transition-all duration-200 focus:border-coral-pulse focus:ring-2 focus:ring-coral-pulse/20" placeholder="Alasan penolakan"></textarea>
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
    </div>

    <div class="mt-4">{{ $disbursements->links() }}</div>
</div>
