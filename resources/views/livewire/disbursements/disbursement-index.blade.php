<div>
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-xl font-semibold">Penyaluran Dana</h1>
        <select wire:model.live="status" class="rounded border border-slate-300 px-3 py-2 text-sm">
            <option value="submitted">Diajukan</option>
            <option value="approved">Disetujui</option>
            <option value="rejected">Ditolak</option>
            <option value="all">Semua</option>
        </select>
    </div>

    <table class="w-full border border-slate-300 text-sm bg-white">
        <thead class="bg-slate-200">
            <tr>
                <th class="border border-slate-300 px-3 py-2 text-left">Program</th>
                <th class="border border-slate-300 px-3 py-2 text-left">Jumlah</th>
                <th class="border border-slate-300 px-3 py-2 text-left">Keterangan</th>
                <th class="border border-slate-300 px-3 py-2 text-left">Diajukan Oleh</th>
                <th class="border border-slate-300 px-3 py-2 text-left">Status</th>
                <th class="border border-slate-300 px-3 py-2 text-left">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($disbursements as $disbursement)
                <tr>
                    <td class="border border-slate-300 px-3 py-2">{{ $disbursement->campaign->name }}</td>
                    <td class="border border-slate-300 px-3 py-2">Rp {{ number_format($disbursement->amount, 0, ',', '.') }}</td>
                    <td class="border border-slate-300 px-3 py-2">{{ $disbursement->description }}</td>
                    <td class="border border-slate-300 px-3 py-2">{{ $disbursement->submitter->name }}</td>
                    <td class="border border-slate-300 px-3 py-2">{{ $disbursement->status->label() }}</td>
                    <td class="border border-slate-300 px-3 py-2 space-x-2">
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
                        <td colspan="6" class="border border-slate-300 px-3 py-3 bg-slate-50">
                            <form wire:submit="confirmReject" class="flex items-start gap-3">
                                <div class="flex-1">
                                    <textarea wire:model="rejectionReason" class="w-full rounded border border-slate-300 px-3 py-2 text-sm" placeholder="Alasan penolakan"></textarea>
                                    @error('rejectionReason') <p class="text-sm text-red-700 mt-1">{{ $message }}</p> @enderror
                                </div>
                                <button type="submit" class="rounded bg-slate-900 px-3 py-2 text-white text-sm">Kirim</button>
                                <button type="button" wire:click="cancelReject" class="rounded border border-slate-300 px-3 py-2 text-sm">Batal</button>
                            </form>
                        </td>
                    </tr>
                @endif
            @empty
                <tr>
                    <td colspan="6" class="border border-slate-300 px-3 py-6 text-center text-slate-500">Tidak ada penyaluran.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="mt-4">{{ $disbursements->links() }}</div>
</div>
