<div>
    <div class="mx-auto grid max-w-6xl gap-x-16 gap-y-8 px-5 py-14 lg:grid-cols-[22rem_1fr]">
        <aside class="lg:sticky lg:top-24 lg:self-start">
            <h1 id="submit-title" class="paint-type text-5xl text-ink sm:text-6xl">Ajukan program donasi</h1>
            <p class="mt-5 text-lg text-ink">Punya kebutuhan yang perlu dibantu banyak orang? Ceritakan di sini. Kami baca tiap pengajuan sebelum program tayang, supaya donatur tidak tertipu.</p>
            <ol class="mt-6 space-y-3 border-t-2 border-ink pt-5 text-sm text-ink-soft">
                <li><strong class="text-ink">Anda mengisi</strong> cerita, target, dan rekening penerima.</li>
                <li><strong class="text-ink">Admin memeriksa</strong> kebenaran dan kelengkapannya.</li>
                <li><strong class="text-ink">Program tayang</strong> dan mulai menerima donasi.</li>
            </ol>
        </aside>

        <div>
        @if ($submitted)
            <div class="border-2 border-paid bg-paid-wash p-6" role="status">
                <h2 class="paint-type text-4xl text-paid">Sudah kami terima</h2>
                <p class="mt-3 max-w-xl text-ink">Cerita Anda sudah masuk. Cek email Anda dan tekan tautan konfirmasi: admin baru bisa menyetujui setelah email terkonfirmasi. Programnya tampil di situs setelah disetujui.</p>
                @if ($trackingCode)
                    <p class="mt-5 text-sm font-semibold text-ink-soft">Simpan kode pengajuan Anda</p>
                    <p class="font-mono text-2xl font-bold text-ink">{{ $trackingCode }}</p>
                    <p class="mt-1 max-w-xl text-sm text-ink-soft">Dengan kode ini Anda bisa melihat status pengajuan kapan saja lewat menu Cek Donasi. Kalau kontak Anda email, kami juga kirim kabar ke sana.</p>
                @endif
                <div class="mt-6 flex flex-wrap gap-3">
                    @if ($trackingCode)
                        <a href="{{ route('program.proposal', $trackingCode) }}" wire:navigate class="btn btn-paint">Lihat status</a>
                    @endif
                    <a href="{{ route('program.index') }}" wire:navigate class="btn btn-ink">Kembali ke program</a>
                </div>
            </div>
        @else
            <form wire:submit="submit" class="space-y-8" novalidate>
                <div class="hidden" aria-hidden="true">
                    <label>Jangan diisi <input type="text" wire:model="website" tabindex="-1" autocomplete="off"></label>
                </div>

                <fieldset class="space-y-5">
                    <legend class="paint-type mb-2 text-3xl">Tentang program</legend>
                    <div>
                        <label for="name" class="label">Judul program</label>
                        <input id="name" type="text" wire:model="name" class="field @error('name') field-error @enderror">
                        @error('name') <p class="error-text" role="alert">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="description" class="label">Ceritakan kebutuhannya</label>
                        <textarea id="description" wire:model="description" rows="6" class="field @error('description') field-error @enderror"></textarea>
                        <p class="hint">Siapa yang dibantu, kenapa mendesak, dan untuk apa dana dipakai.</p>
                        @error('description') <p class="error-text" role="alert">{{ $message }}</p> @enderror
                    </div>
                    <div class="grid gap-5 sm:grid-cols-2">
                        <div
                            x-data="{ raw: $wire.entangle('target_amount'), fmt(v) { return v ? new Intl.NumberFormat('id-ID').format(v) : ''; } }">
                            <label for="target_amount" class="label">Target dana</label>
                            <div class="relative">
                                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 font-bold text-ink-soft">Rp</span>
                                <input id="target_amount" type="text" inputmode="numeric" :value="fmt(raw)" @input="raw = parseInt($event.target.value.replace(/[^0-9]/g, '')) || 0" class="field pl-11 @error('target_amount') field-error @enderror">
                            </div>
                            @error('target_amount') <p class="error-text" role="alert">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="duration_days" class="label">Lama penggalangan</label>
                            <select id="duration_days" wire:model="duration_days" class="field">
                                @foreach ([14 => '14 hari', 30 => '30 hari', 60 => '60 hari', 90 => '90 hari'] as $days => $label)
                                    <option value="{{ $days }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('duration_days') <p class="error-text" role="alert">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div>
                        <label for="cover" class="label">Foto sampul (opsional, jpg/png, maks 2MB)</label>
                        @if ($cover_image_upload && $cover_image_upload->isPreviewable())
                            <img src="{{ $cover_image_upload->temporaryUrl() }}" alt="" class="mb-2 h-40 w-full border-2 border-ink object-cover">
                        @endif
                        <input id="cover" type="file" wire:model="cover_image_upload" accept="image/png,image/jpeg" class="field file:mr-3 file:border-0 file:bg-ink file:px-3 file:py-1.5 file:font-bold file:text-board">
                        @error('cover_image_upload') <p class="error-text" role="alert">{{ $message }}</p> @enderror
                    </div>
                </fieldset>

                <fieldset class="space-y-5">
                    <legend class="paint-type mb-2 text-3xl">Penerima dana</legend>
                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <label for="bank_name" class="label">Nama bank</label>
                            <select id="bank_name" wire:model="bank_name" class="field @error('bank_name') field-error @enderror">
                                <option value="">Pilih bank</option>
                                @foreach (App\Support\Banks::all() as $bank)
                                    <option value="{{ $bank }}">{{ $bank }}</option>
                                @endforeach
                            </select>
                            @error('bank_name') <p class="error-text" role="alert">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="account_number" class="label">Nomor rekening</label>
                            <input id="account_number" type="text" inputmode="numeric" wire:model.blur="account_number" class="field @error('account_number') field-error @enderror">
                            @error('account_number') <p class="error-text" role="alert">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div>
                        <label for="account_holder" class="label">Nama pemilik rekening</label>
                        <input id="account_holder" type="text" wire:model="account_holder" class="field @error('account_holder') field-error @enderror">
                        @error('account_holder') <p class="error-text" role="alert">{{ $message }}</p> @enderror
                    </div>
                </fieldset>

                <fieldset class="space-y-5">
                    <legend class="paint-type mb-2 text-3xl">Kontak pengaju</legend>
                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <label for="proposer_name" class="label">Nama Anda</label>
                            <input id="proposer_name" type="text" wire:model.blur="proposer_name" autocomplete="name" class="field @error('proposer_name') field-error @enderror">
                            @error('proposer_name') <p class="error-text" role="alert">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="proposer_email" class="label">Email</label>
                            <input id="proposer_email" type="email" wire:model.blur="proposer_email" autocomplete="email" class="field @error('proposer_email') field-error @enderror">
                            <p class="hint">Kami kirim tautan konfirmasi ke sini.</p>
                            @error('proposer_email') <p class="error-text" role="alert">{{ $message }}</p> @enderror
                        </div>
                    </div>
                <div class="sm:max-w-xs">
                        <label for="proposer_phone" class="label">WhatsApp (opsional)</label>
                        <input id="proposer_phone" type="text" inputmode="tel" wire:model.blur="proposer_phone" placeholder="08123456789" class="field @error('proposer_phone') field-error @enderror">
                        @error('proposer_phone') <p class="error-text" role="alert">{{ $message }}</p> @enderror
                    </div>
                </fieldset>

                <button type="submit" class="btn btn-paint btn-lg" wire:loading.attr="disabled" wire:target="submit">
                    <span wire:loading.remove wire:target="submit">Kirim pengajuan</span>
                    <span wire:loading wire:target="submit">Mengirim&hellip;</span>
                </button>
            </form>
        @endif
        </div>
    </div>
</div>
