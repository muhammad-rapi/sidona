<div class="max-w-xl">
    <h1 class="text-xl font-semibold mb-2">Cek Keaslian Laporan</h1>
    <p class="text-sm text-ink-muted mb-6">Unggah berkas PDF laporan SIDONA untuk memverifikasi checksum-nya masih cocok.</p>

    <form wire:submit="check" class="space-y-4 bg-white p-6 rounded border border-line">
        <div>
            <label class="block text-sm font-medium mb-1">Berkas PDF</label>
            <input type="file" wire:model="file" accept="application/pdf" class="w-full rounded border border-line px-3 py-2">
            @error('file') <p class="text-sm text-red-700 mt-1">{{ $message }}</p> @enderror
        </div>

        <button type="submit" class="rounded-md bg-ink px-4 py-2 text-white text-sm">Cek Keaslian</button>
    </form>

    @if ($result)
        @if ($result['valid'])
            <div class="mt-6 bg-white p-6 rounded border border-verified">
                <p class="text-sm text-verified">Laporan asli, tidak ada perubahan sejak diterbitkan.</p>
                @if ($result['export'] ?? null)
                    <p class="text-sm text-ink-muted mt-2">
                        Diekspor oleh {{ $result['export']->user->name }}
                        pada {{ $result['export']->created_at->format('d/m/Y H:i') }}
                        ({{ $result['export']->report_type }}).
                    </p>
                @endif
            </div>
        @else
            <div class="mt-6 bg-white p-6 rounded border border-red-700">
                <p class="text-sm text-red-800">Berkas ini bukan laporan SIDONA yang sah, atau telah diubah sejak diterbitkan.</p>
            </div>
        @endif
    @endif
</div>
