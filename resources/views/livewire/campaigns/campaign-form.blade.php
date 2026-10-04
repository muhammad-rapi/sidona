<div class="max-w-5xl">
    <div class="page-head">
        <div>
            <h1 class="page-title">{{ $campaign ? 'Ubah program donasi' : 'Tambah program donasi' }}</h1>
            @if ($campaign?->proposer_name)
                <p class="page-sub">Diajukan oleh {{ $campaign->proposer_name }}. Perubahan di sini tercatat di log audit.</p>
            @endif
        </div>
    </div>

    <form wire:submit="save">
        <div class="grid gap-x-14 gap-y-10 lg:grid-cols-[1fr_21rem] lg:items-start">
            <div class="space-y-10">
                <section class="space-y-5" aria-labelledby="f-cerita">
                    <h2 id="f-cerita" class="border-b-2 border-ink pb-2 text-xl font-extrabold tracking-tight">Cerita program</h2>
                    <div>
                        <label for="name" class="label">Nama program</label>
                        <input id="name" type="text" wire:model="name" class="field @error('name') field-error @enderror">
                        @error('name') <p class="error-text">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="description" class="label">Deskripsi</label>
                        <textarea id="description" rows="7" wire:model="description" class="field @error('description') field-error @enderror"></textarea>
                        <p class="hint">Siapa yang dibantu, kenapa mendesak, dan untuk apa dana dipakai.</p>
                        @error('description') <p class="error-text">{{ $message }}</p> @enderror
                    </div>
                </section>

                <section class="space-y-5" aria-labelledby="f-dana">
                    <h2 id="f-dana" class="border-b-2 border-ink pb-2 text-xl font-extrabold tracking-tight">Dana dan rekening</h2>
        <div class="sm:max-w-xs">
            <label for="target" class="label">Target dana</label>
            <div
                x-data="{
                    raw: $wire.entangle('target_amount'),
                    formatted: '',
                    format(v) { return v ? new Intl.NumberFormat('id-ID').format(v) : ''; },
                    init() { this.formatted = this.format(this.raw); },
                }"
                class="relative"
            >
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-sm font-bold text-ink-soft">Rp</span>
                <input
                    id="target"
                    type="text"
                    inputmode="numeric"
                    x-model="formatted"
                    x-on:input="
                        let digits = $event.target.value.replace(/[^0-9]/g, '');
                        raw = digits ? parseInt(digits) : 0;
                        formatted = format(raw);
                    "
                    placeholder="0"
                    class="field pl-10"
                >
            </div>
            @error('target_amount') <p class="error-text">{{ $message }}</p> @enderror
        </div>


                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <label for="bank_name" class="label">Bank atau e-wallet</label>
                            <select id="bank_name" wire:model="bank_name" class="field @error('bank_name') field-error @enderror">
                                <option value="">Pilih bank</option>
                                @foreach (App\Support\Banks::with($campaign?->bank_name) as $bank)
                                    <option value="{{ $bank }}">{{ $bank }}</option>
                                @endforeach
                            </select>
                            @error('bank_name') <p class="error-text">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="account_number" class="label">Nomor rekening</label>
                            <input id="account_number" type="text" wire:model="account_number" class="field font-mono @error('account_number') field-error @enderror">
                            @error('account_number') <p class="error-text">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div>
                        <label for="account_holder" class="label">Atas nama</label>
                        <input id="account_holder" type="text" wire:model="account_holder" class="field @error('account_holder') field-error @enderror">
                        @error('account_holder') <p class="error-text">{{ $message }}</p> @enderror
                    </div>
                </section>

                <section class="space-y-5" aria-labelledby="f-masa">
                    <h2 id="f-masa" class="border-b-2 border-ink pb-2 text-xl font-extrabold tracking-tight">Masa penggalangan</h2>
                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <label for="starts_on" class="label">Mulai</label>
                            <input id="starts_on" type="date" wire:model="starts_on" class="field @error('starts_on') field-error @enderror">
                            @error('starts_on') <p class="error-text">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="ends_on" class="label">Selesai</label>
                            <input id="ends_on" type="date" wire:model="ends_on" class="field @error('ends_on') field-error @enderror">
                            @error('ends_on') <p class="error-text">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </section>
            </div>

            <aside class="space-y-8 lg:sticky lg:top-8">
                <section class="space-y-5" aria-labelledby="f-foto">
                    <h2 id="f-foto" class="border-b-2 border-ink pb-2 text-xl font-extrabold tracking-tight">Foto</h2>
        <div>
            <label for="cover" class="label">Foto sampul</label>

            @if ($cover_image_upload)
                <div class="relative mb-2">
                    <img src="{{ $cover_image_upload->temporaryUrl() }}" class="h-36 w-full object-cover">
                    <p class="hint">Foto baru akan disimpan saat form dikirim.</p>
                    <button type="button" wire:click="$set('cover_image_upload', null)" class="btn btn-sm btn-line absolute top-2 right-2 bg-paper text-paint-dark">Batalkan</button>
                </div>
            @elseif ($campaign?->cover_image && ! $remove_cover_image)
                <div class="relative mb-2">
                    <img src="{{ Illuminate\Support\Facades\Storage::url($campaign->cover_image) }}" class="h-36 w-full object-cover">
                    <button type="button" wire:click="$set('remove_cover_image', true)" class="btn btn-sm btn-line absolute top-2 right-2 bg-paper text-paint-dark">Hapus Foto</button>
                </div>
            @elseif ($remove_cover_image)
                <div class="mb-2 flex items-center justify-between border border-dashed border-ink-faint bg-board-wash px-4 py-3 text-sm text-ink-soft">
                    Foto sampul akan dihapus saat disimpan.
                    <button type="button" wire:click="$set('remove_cover_image', false)" class="font-bold text-ink underline">Batalkan</button>
                </div>
            @endif

            <input id="cover" type="file" wire:model="cover_image_upload" class="field file:mr-3 file:border-0 file:bg-ink file:px-3 file:py-1.5 file:text-board file:font-bold">
            <p class="hint">jpg atau png, maks 2MB.</p>
            @error('cover_image_upload') <p class="error-text">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="gallery" class="label">Galeri foto</label>

            @if ($photos->isNotEmpty())
                <ul class="mb-3 grid grid-cols-3 gap-2">
                    @foreach ($photos as $photo)
                        <li class="relative aspect-square border-2 border-ink bg-board">
                            <img src="{{ $photo->url() }}" alt="" class="h-full w-full object-cover" loading="lazy">
                            <button type="button" wire:click="removePhoto({{ $photo->id }})" wire:confirm="Hapus foto ini dari galeri?" class="absolute right-1 top-1 flex h-8 w-8 items-center justify-center border-2 border-ink bg-paper text-paint-dark hover:bg-paint hover:text-white" aria-label="Hapus foto">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="square"><path d="M6 6l12 12M18 6 6 18"/></svg>
                            </button>
                        </li>
                    @endforeach
                </ul>
            @endif

            @if ($gallery_uploads)
                <ul class="mb-3 grid grid-cols-3 gap-2">
                    @foreach ($gallery_uploads as $i => $upload)
                        <li class="relative aspect-square border-2 border-dashed border-ink">
                            @if ($upload->isPreviewable())
                                <img src="{{ $upload->temporaryUrl() }}" alt="" class="h-full w-full object-cover">
                            @else
                                <span class="flex h-full w-full items-center justify-center break-all p-2 text-xs text-paint-dark">{{ $upload->getClientOriginalName() }}</span>
                            @endif
                            <button type="button" wire:click="removeUpload({{ $i }})" class="absolute right-1 top-1 flex h-8 w-8 items-center justify-center border-2 border-ink bg-paper" aria-label="Batalkan foto">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="square"><path d="M6 6l12 12M18 6 6 18"/></svg>
                            </button>
                        </li>
                    @endforeach
                </ul>
                <p class="hint mb-2">Foto baru disimpan saat form dikirim.</p>
            @endif

            <input id="gallery" type="file" wire:model="gallery_uploads" multiple accept="image/png,image/jpeg" class="field file:mr-3 file:border-0 file:bg-ink file:px-3 file:py-1.5 file:text-board file:font-bold">
            <p class="hint">Maks 12 foto, 2MB per foto.</p>
            <div wire:loading wire:target="gallery_uploads" class="hint">Mengunggah&hellip;</div>
            @error('gallery_uploads') <p class="error-text">{{ $message }}</p> @enderror
            @error('gallery_uploads.*') <p class="error-text">{{ $message }}</p> @enderror
        </div>

                </section>

                <section aria-labelledby="f-pic">
                    <h2 id="f-pic" class="mb-4 border-b-2 border-ink pb-2 text-xl font-extrabold tracking-tight">Penanggung jawab</h2>
        <fieldset>
            <legend class="sr-only">Jenis penanggung jawab</legend>
            <div class="mb-3 grid grid-cols-2 gap-2">
                <label class="cursor-pointer">
                    <input type="radio" wire:model.live="pic_mode" value="staff" class="peer sr-only">
                    <span class="block border-2 border-ink px-3 py-2.5 text-center text-sm font-bold peer-checked:bg-ink peer-checked:text-board peer-focus-visible:outline peer-focus-visible:outline-[3px] peer-focus-visible:outline-paint">Staf SIDONA</span>
                </label>
                <label class="cursor-pointer">
                    <input type="radio" wire:model.live="pic_mode" value="external" class="peer sr-only">
                    <span class="block border-2 border-ink px-3 py-2.5 text-center text-sm font-bold peer-checked:bg-ink peer-checked:text-board peer-focus-visible:outline peer-focus-visible:outline-[3px] peer-focus-visible:outline-paint">Pihak luar</span>
                </label>
            </div>

            @if ($pic_mode === 'external')
                <div class="grid gap-3">
                    <div>
                        <label for="pic_name" class="sr-only">Nama PIC</label>
                        <input id="pic_name" type="text" wire:model="pic_name" placeholder="Nama PIC, mis. Ibu Ratna" class="field @error('pic_name') field-error @enderror">
                        @error('pic_name') <p class="error-text">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="pic_contact" class="sr-only">Kontak PIC</label>
                        <input id="pic_contact" type="text" wire:model="pic_contact" placeholder="Email atau WhatsApp" class="field @error('pic_contact') field-error @enderror">
                        @error('pic_contact') <p class="error-text">{{ $message }}</p> @enderror
                    </div>
                </div>
            @else
                <select id="pic" wire:model="pic_user_id" aria-label="Staf penanggung jawab" class="field @error('pic_user_id') field-error @enderror">
                    <option value="">Pilih staf</option>
                    @foreach ($picOptions as $option)
                        <option value="{{ $option->id }}">{{ $option->name }} ({{ $option->role->label() }})</option>
                    @endforeach
                </select>
                @error('pic_user_id') <p class="error-text">{{ $message }}</p> @enderror
            @endif
            <p class="hint">Nama PIC tampil di halaman program publik. Kontaknya hanya terlihat oleh staf.@if ($campaign?->proposer_name && $pic_mode === 'staff') Dikosongkan, PIC-nya tetap pengaju: {{ $campaign->proposer_name }}.@endif</p>
        </fieldset>

                </section>
            </aside>
        </div>

        <div class="sticky bottom-0 -mx-4 mt-10 flex flex-wrap items-center gap-3 border-t border-ink/20 bg-desk/95 px-4 py-4 backdrop-blur-none sm:-mx-8 sm:px-8">
            <button type="submit" class="btn btn-paint" wire:loading.attr="disabled" wire:target="save">
                <span wire:loading.remove wire:target="save">Simpan program</span>
                <span wire:loading wire:target="save">Menyimpan&hellip;</span>
            </button>
            <a href="{{ route('campaigns.index') }}" wire:navigate class="btn btn-line">Batal</a>
        </div>
    </form>
</div>
