<div class="mx-auto grid max-w-6xl gap-x-16 gap-y-10 px-5 py-14 md:py-20 lg:grid-cols-[1fr_1.1fr] lg:items-start">
    <div>
        <h1 class="paint-type text-6xl text-ink sm:text-8xl">Cek donasi</h1>
        <p class="mt-5 max-w-sm text-lg text-ink">Punya kode yang diawali DON- atau PRG-? Tempel di sini. Kuitansi donasi atau status pengajuan program langsung terbuka.</p>
        <p class="mt-3 max-w-sm text-sm text-ink-soft">Kode ada di layar setelah membayar, dan di email kuitansi kalau Anda mengisi email.</p>
    </div>

    <form wire:submit="check" class="border-2 border-ink bg-board p-6 sm:p-8">
        <label for="reference_code" class="label">Kode</label>
        <input id="reference_code" type="text" wire:model="reference_code" placeholder="DON-XXXXXXXX" autocomplete="off" autocapitalize="characters" class="field font-mono text-xl uppercase tracking-wider @if ($notFound || $errors->has('reference_code')) field-error @endif">
        @error('reference_code') <p class="error-text" role="alert">Kode kuitansi wajib diisi.</p> @enderror
        @if ($notFound)
            <p class="error-text" role="alert">Kode tidak ditemukan. Periksa lagi huruf dan angkanya.</p>
        @endif
        <button type="submit" class="btn btn-ink btn-lg mt-5 w-full sm:w-auto">Buka</button>
    </form>
</div>
