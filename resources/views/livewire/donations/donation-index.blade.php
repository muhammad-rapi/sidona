<div>
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-xl font-semibold">Donasi Masuk</h1>
        <select wire:model.live="status" class="rounded border border-slate-300 px-3 py-2 text-sm">
            <option value="pending">Menunggu</option>
            <option value="verified">Terverifikasi</option>
            <option value="rejected">Ditolak</option>
            <option value="all">Semua</option>
        </select>
    </div>

    <table class="w-full border border-slate-300 text-sm bg-white">
        <thead class="bg-slate-200">
            <tr>
                <th class="border border-slate-300 px-3 py-2 text-left">Kode</th>
                <th class="border border-slate-300 px-3 py-2 text-left">Program</th>
                <th class="border border-slate-300 px-3 py-2 text-left">Donatur</th>
                <th class="border border-slate-300 px-3 py-2 text-left">Nominal</th>
                <th class="border border-slate-300 px-3 py-2 text-left">Bukti</th>
                <th class="border border-slate-300 px-3 py-2 text-left">Status</th>
                <th class="border border-slate-300 px-3 py-2 text-left">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($donations as $donation)
                <tr>
                    <td class="border border-slate-300 px-3 py-2 font-mono">{{ $donation->reference_code }}</td>
                    <td class="border border-slate-300 px-3 py-2">{{ $donation->campaign->name }}</td>
                    <td class="border border-slate-300 px-3 py-2">{{ $donation->donor_name }}</td>
                    <td class="border border-slate-300 px-3 py-2">Rp {{ number_format($donation->amount, 0, ',', '.') }}</td>
                    <td class="border border-slate-300 px-3 py-2">
                        <a href="{{ Illuminate\Support\Facades\Storage::url($donation->proof_path) }}" target="_blank" rel="noopener">Lihat</a>
                    </td>
                    <td class="border border-slate-300 px-3 py-2">{{ $donation->status->label() }}</td>
                    <td class="border border-slate-300 px-3 py-2 space-x-2">
                        @can('verify', $donation)
                            <button type="button" wire:click="verify({{ $donation->id }})" wire:confirm="Verifikasi donasi ini?">Verifikasi</button>
                        @endcan
                        @can('reject', $donation)
                            <button type="button" wire:click="startReject({{ $donation->id }})">Tolak</button>
                        @endcan
                    </td>
                </tr>
                @if ($rejectingId === $donation->id)
                    <tr>
                        <td colspan="7" class="border border-slate-300 px-3 py-3 bg-slate-50">
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
                    <td colspan="7" class="border border-slate-300 px-3 py-6 text-center text-slate-500">Tidak ada donasi.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="mt-4">{{ $donations->links() }}</div>
</div>
