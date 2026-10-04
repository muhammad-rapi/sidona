<div class="mx-auto grid max-w-6xl gap-x-16 gap-y-8 px-5 py-14 lg:grid-cols-[22rem_1fr]">
    <aside class="lg:sticky lg:top-24 lg:self-start">
        <h1 class="paint-type text-5xl text-ink sm:text-6xl">Lacak tiket</h1>
        <p class="mt-5 text-lg text-ink">Kehilangan tautan percakapan? Masukkan kode tiket dan email Anda. Kami kirim ulang tautan pribadinya ke email itu.</p>
        <p class="mt-4 border-t-2 border-ink pt-4 text-sm text-ink-soft">Belum punya tiket? <a href="{{ route('support.new') }}" wire:navigate class="font-bold text-ink underline underline-offset-4">Hubungi kami</a>.</p>
    </aside>

    <div class="max-w-xl">
        @if ($sent)
            <div class="border-2 border-paid bg-paid-wash p-6" role="status">
                <h2 class="text-2xl font-extrabold tracking-tight text-paid">Cek email Anda</h2>
                <p class="mt-3 text-ink">Kalau kode dan email cocok, tautan percakapan sudah kami kirim. Kalau belum sampai dalam beberapa menit, periksa folder spam atau pastikan emailnya sama dengan yang Anda pakai saat menulis pesan.</p>
                <button type="button" wire:click="$set('sent', false)" class="btn btn-ink mt-5">Coba kode lain</button>
            </div>
        @else
            <form wire:submit="track" class="space-y-5" novalidate>
                <div>
                    <label for="k-code" class="label">Kode tiket</label>
                    <input id="k-code" type="text" wire:model="code" placeholder="TKT-..." autocomplete="off" autocapitalize="characters" class="field font-mono text-lg uppercase tracking-wider @error('code') field-error @enderror">
                    @error('code') <p class="error-text" role="alert">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="k-email" class="label">Email</label>
                    <input id="k-email" type="email" wire:model="email" autocomplete="email" class="field @error('email') field-error @enderror">
                    @error('email') <p class="error-text" role="alert">{{ $message }}</p> @enderror
                </div>
                <button type="submit" class="btn btn-paint btn-lg" wire:loading.attr="disabled" wire:target="track">Kirim tautan ke email</button>
            </form>
        @endif
    </div>
</div>
