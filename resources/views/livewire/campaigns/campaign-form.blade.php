<div class="max-w-2xl">
    <div class="page-head">
        <div>
            <h1 class="page-title">{{ $campaign ? 'Ubah Program Donasi' : 'Tambah Program Donasi' }}</h1>
        </div>
    </div>

    <form wire:submit="save" class="panel space-y-4 p-5">
        <div>
            <label class="label">Nama Program</label>
            <input type="text" wire:model="name" class="field">
            @error('name') <p class="error-text">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="label">Deskripsi</label>
            <textarea wire:model="description" class="field"></textarea>
            @error('description') <p class="error-text">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="label">Foto Sampul (jpg/png, maks 2MB)</label>

            @if ($cover_image_upload)
                <div class="relative mb-2">
                    <img src="{{ $cover_image_upload->temporaryUrl() }}" class="h-32 w-full object-cover">
                    <p class="hint">Foto baru akan disimpan saat form dikirim.</p>
                    <button type="button" wire:click="$set('cover_image_upload', null)" class="btn btn-sm btn-line absolute top-2 right-2 bg-paper text-paint-dark">Batalkan</button>
                </div>
            @elseif ($campaign?->cover_image && ! $remove_cover_image)
                <div class="relative mb-2">
                    <img src="{{ Illuminate\Support\Facades\Storage::url($campaign->cover_image) }}" class="h-32 w-full object-cover">
                    <button type="button" wire:click="$set('remove_cover_image', true)" class="btn btn-sm btn-line absolute top-2 right-2 bg-paper text-paint-dark">Hapus Foto</button>
                </div>
            @elseif ($remove_cover_image)
                <div class="mb-2 flex items-center justify-between border border-dashed border-ink-faint bg-board-wash px-4 py-3 text-sm text-ink-soft">
                    Foto sampul akan dihapus saat disimpan.
                    <button type="button" wire:click="$set('remove_cover_image', false)" class="font-bold text-ink underline">Batalkan</button>
                </div>
            @endif

            <input type="file" wire:model="cover_image_upload" class="field file:mr-3 file:border-0 file:bg-ink file:px-3 file:py-1.5 file:text-board file:font-bold">
            @error('cover_image_upload') <p class="error-text">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="label">Galeri Foto (jpg/png, maks 2MB per foto, maks 12 foto)</label>

            @if ($photos->isNotEmpty())
                <ul class="mb-3 grid grid-cols-3 gap-2 sm:grid-cols-4">
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
                <ul class="mb-3 grid grid-cols-3 gap-2 sm:grid-cols-4">
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

            <input type="file" wire:model="gallery_uploads" multiple accept="image/png,image/jpeg" class="field file:mr-3 file:border-0 file:bg-ink file:px-3 file:py-1.5 file:text-board file:font-bold">
            <div wire:loading wire:target="gallery_uploads" class="hint">Mengunggah&hellip;</div>
            @error('gallery_uploads') <p class="error-text">{{ $message }}</p> @enderror
            @error('gallery_uploads.*') <p class="error-text">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="label">Target Dana</label>
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

        <div class="space-y-4 border border-rule bg-board-wash p-4">
            <p class="font-bold text-ink">Rekening Tujuan Donasi</p>

            <div>
                <label class="label">Nama Bank / E-Wallet</label>
                <input type="text" wire:model="bank_name" placeholder="mis. BCA, Mandiri, GoPay" class="field">
                @error('bank_name') <p class="error-text">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="label">Nomor Rekening</label>
                <input type="text" wire:model="account_number" class="field font-mono">
                @error('account_number') <p class="error-text">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="label">Atas Nama</label>
                <input type="text" wire:model="account_holder" class="field">
                @error('account_holder') <p class="error-text">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <label class="label">Tanggal Mulai</label>
                <input type="date" wire:model="starts_on" class="field">
                @error('starts_on') <p class="error-text">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="label">Tanggal Selesai</label>
                <input type="date" wire:model="ends_on" class="field">
                @error('ends_on') <p class="error-text">{{ $message }}</p> @enderror
            </div>
        </div>

        <button type="submit" class="btn btn-paint">Simpan</button>
    </form>
</div>
