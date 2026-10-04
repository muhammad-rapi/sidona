<div>
    <section class="on-board bg-board" aria-labelledby="check-title">
        <div class="mx-auto max-w-3xl px-5 py-12 md:py-16">
            <h1 id="check-title" class="paint-type rise-in text-6xl text-ink sm:text-8xl">Cek donasi</h1>
            <p class="mt-4 max-w-md text-lg font-semibold text-ink">Masukkan kode kuitansi untuk membuka lagi kuitansi dan status donasi Anda.</p>
        </div>
        <div class="h-1 bg-ink"></div>
    </section>

    <div class="mx-auto max-w-3xl px-5 py-10">
        <form wire:submit="check" class="max-w-xl">
            <label for="reference_code" class="label">Kode kuitansi</label>
            <div class="flex flex-col gap-3 sm:flex-row">
                <input id="reference_code" type="text" wire:model="reference_code" placeholder="DON-XXXXXXXX" autocomplete="off" autocapitalize="characters" class="field font-mono text-lg uppercase @if ($notFound || $errors->has('reference_code')) field-error @endif">
                <button type="submit" class="btn btn-ink btn-lg shrink-0">Buka</button>
            </div>
            @error('reference_code') <p class="error-text" role="alert">Kode kuitansi wajib diisi.</p> @enderror
            @if ($notFound)
                <p class="error-text" role="alert">Kode tidak ditemukan. Periksa lagi huruf dan angkanya, kode diawali DON-.</p>
            @endif
        </form>
    </div>
</div>
