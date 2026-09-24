<div>
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-xl font-semibold">Donasi Masuk</h1>
        <select wire:model.live="status" class="rounded border border-line px-3 py-2 text-sm">
            <option value="pending">Menunggu</option>
            <option value="verified">Terverifikasi</option>
            <option value="rejected">Ditolak</option>
            <option value="all">Semua</option>
        </select>
    </div>

    <table class="w-full text-sm">
        <thead>
            <tr>
                <th class="border-b-2 border-ink px-4 py-3 text-left font-medium text-ink-muted">Kode</th>
                <th class="border-b-2 border-ink px-4 py-3 text-left font-medium text-ink-muted">Program</th>
                <th class="border-b-2 border-ink px-4 py-3 text-left font-medium text-ink-muted">Donatur</th>
                <th class="border-b-2 border-ink px-4 py-3 text-left font-medium text-ink-muted">Nominal</th>
                <th class="border-b-2 border-ink px-4 py-3 text-left font-medium text-ink-muted">Bukti</th>
                <th class="border-b-2 border-ink px-4 py-3 text-left font-medium text-ink-muted">Status</th>
                <th class="border-b-2 border-ink px-4 py-3 text-left font-medium text-ink-muted">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($donations as $donation)
                <tr>
                    <td class="border-b border-line px-4 py-3 font-mono">{{ $donation->reference_code }}</td>
                    <td class="border-b border-line px-4 py-3">{{ $donation->campaign->name }}</td>
                    <td class="border-b border-line px-4 py-3">{{ $donation->donor_name }}</td>
                    <td class="border-b border-line px-4 py-3 font-mono">Rp {{ number_format($donation->amount, 0, ',', '.') }}</td>
                    <td class="border-b border-line px-4 py-3">
                        <a href="{{ Illuminate\Support\Facades\Storage::url($donation->proof_path) }}" target="_blank" rel="noopener">Lihat</a>
                    </td>
                    <td class="border-b border-line px-4 py-3">
                        @php($statusColor = match ($donation->status->value) { 'verified' => 'text-verified', 'rejected' => 'text-flagged', default => 'text-ink-muted' })
                        <span class="{{ $statusColor }}">&bull; {{ $donation->status->label() }}</span>
                    </td>
                    <td class="border-b border-line px-4 py-3 space-x-2">
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
                        <td colspan="7" class="border-b border-line px-4 py-3 bg-paper">
                            <form wire:submit="confirmReject" class="flex items-start gap-3">
                                <div class="flex-1">
                                    <textarea wire:model="rejectionReason" class="w-full rounded border border-line px-3 py-2 text-sm" placeholder="Alasan penolakan"></textarea>
                                    @error('rejectionReason') <p class="text-sm text-red-700 mt-1">{{ $message }}</p> @enderror
                                </div>
                                <button type="submit" class="rounded-md bg-ink px-3 py-2 text-white text-sm">Kirim</button>
                                <button type="button" wire:click="cancelReject" class="rounded border border-line px-3 py-2 text-sm">Batal</button>
                            </form>
                        </td>
                    </tr>
                @endif
            @empty
                <tr>
                    <td colspan="7" class="border-b border-line px-4 py-6 text-center text-ink-muted">Tidak ada donasi.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="mt-4">{{ $donations->links() }}</div>
</div>
