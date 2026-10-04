<div class="mx-auto grid max-w-6xl gap-x-16 gap-y-8 px-5 py-14 lg:grid-cols-[22rem_1fr]">
    <aside class="lg:sticky lg:top-24 lg:self-start">
        <h1 class="paint-type text-5xl text-ink sm:text-6xl">Hubungi kami</h1>
        <p class="mt-5 text-lg text-ink">Ada donasi yang bermasalah, pembayaran yang macet, atau pertanyaan soal pengajuan program? Tulis di sini. Kami balas lewat email.</p>
        <p class="mt-4 border-t-2 border-ink pt-4 text-sm text-ink-soft">Sudah pernah menulis? <a href="{{ route('support.track') }}" wire:navigate class="font-bold text-ink underline underline-offset-4">Lacak tiket Anda</a>.</p>
        <p class="mt-3 text-sm text-ink-soft">Cek dulu <a href="{{ route('faq') }}" wire:navigate class="font-bold text-ink underline underline-offset-4">pertanyaan umum</a>, mungkin jawabannya sudah ada.</p>
    </aside>

    <form wire:submit="submit" class="max-w-2xl space-y-6" novalidate>
        <div class="hidden" aria-hidden="true">
            <label>Jangan diisi <input type="text" wire:model="website" tabindex="-1" autocomplete="off"></label>
        </div>

        <div class="grid gap-5 sm:grid-cols-2">
            <div>
                <label for="t-name" class="label">Nama</label>
                <input id="t-name" type="text" wire:model="name" autocomplete="name" class="field @error('name') field-error @enderror">
                @error('name') <p class="error-text" role="alert">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="t-email" class="label">Email</label>
                <input id="t-email" type="email" wire:model="email" autocomplete="email" class="field @error('email') field-error @enderror">
                @error('email') <p class="error-text" role="alert">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="grid gap-5 sm:grid-cols-2">
            <div>
                <label for="t-category" class="label">Soal apa?</label>
                <select id="t-category" wire:model="category" class="field">
                    @foreach ($categories as $option)
                        <option value="{{ $option->value }}">{{ $option->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="t-code" class="label">Kode terkait (opsional)</label>
                <input id="t-code" type="text" wire:model="related_code" placeholder="DON-... atau PRG-..." class="field font-mono uppercase @error('related_code') field-error @enderror">
                @error('related_code') <p class="error-text" role="alert">{{ $message }}</p> @enderror
            </div>
        </div>

        <div>
            <label for="t-subject" class="label">Judul singkat</label>
            <input id="t-subject" type="text" wire:model="subject" class="field @error('subject') field-error @enderror">
            @error('subject') <p class="error-text" role="alert">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="t-message" class="label">Ceritakan masalahnya</label>
            <textarea id="t-message" wire:model="message" rows="7" class="field @error('message') field-error @enderror"></textarea>
            <p class="hint">Sertakan kode donasi atau pengajuan kalau ada. Jangan tulis kata sandi atau data kartu.</p>
            @error('message') <p class="error-text" role="alert">{{ $message }}</p> @enderror
        </div>

        <button type="submit" class="btn btn-paint btn-lg" wire:loading.attr="disabled" wire:target="submit">
            <span wire:loading.remove wire:target="submit">Kirim pesan</span>
            <span wire:loading wire:target="submit">Mengirim&hellip;</span>
        </button>
    </form>
</div>
