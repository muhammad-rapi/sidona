<div class="max-w-2xl">
    <div class="page-head">
        <div>
            <h1 class="page-title">Cek Keaslian Laporan</h1>
            <p class="page-sub">Unggah berkas PDF laporan SIDONA untuk memverifikasi checksum-nya masih cocok.</p>
        </div>
    </div>

    <form wire:submit="check" class="panel p-5 space-y-4">
        <div>
            <label class="label">Berkas PDF</label>
            <input type="file" wire:model="file" accept="application/pdf" class="field @error('file') field-error @enderror">
            @error('file') <p class="error-text">{{ $message }}</p> @enderror
        </div>

        <button type="submit" class="btn btn-paint">Cek Keaslian</button>
    </form>

    @if ($result)
        @if ($result['valid'])
            <div class="mt-6 bg-paid-wash border-2 border-paid text-paid p-5">
                <p class="font-bold text-lg">Laporan asli, tidak ada perubahan sejak diterbitkan.</p>
                @if ($result['export'] ?? null)
                    <p class="text-sm text-ink mt-2">
                        Diekspor oleh {{ $result['export']->user->name }}
                        pada {{ $result['export']->created_at->format('d/m/Y H:i') }}
                        ({{ $result['export']->report_type }}).
                    </p>
                @endif
            </div>
        @else
            <div class="mt-6 bg-paint-wash border-2 border-paint text-paint-dark p-5">
                <p class="font-bold text-lg">Berkas ini bukan laporan SIDONA yang sah, atau telah diubah sejak diterbitkan.</p>
            </div>
        @endif
    @endif
</div>
