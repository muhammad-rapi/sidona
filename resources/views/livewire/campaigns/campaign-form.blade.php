<div class="max-w-xl">
    <h1 class="text-xl font-semibold mb-6">{{ $campaign ? 'Ubah Program Donasi' : 'Tambah Program Donasi' }}</h1>

    <form wire:submit="save" class="space-y-4 bg-white p-6 rounded-2xl border border-frost-gray">
        <div>
            <label class="block text-sm font-medium mb-1">Nama Program</label>
            <input type="text" wire:model="name" class="w-full rounded border border-frost-gray px-3 py-2">
            @error('name') <p class="text-sm text-red-700 mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Deskripsi</label>
            <textarea wire:model="description" class="w-full rounded border border-frost-gray px-3 py-2"></textarea>
            @error('description') <p class="text-sm text-red-700 mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Target Dana</label>
            <div
                x-data="{
                    raw: $wire.entangle('target_amount'),
                    formatted: '',
                    format(v) { return v ? new Intl.NumberFormat('id-ID').format(v) : ''; },
                    init() { this.formatted = this.format(this.raw); },
                }"
                class="relative"
            >
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-sm text-graphite">Rp</span>
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
                    class="w-full rounded border border-frost-gray pl-9 pr-3 py-2"
                >
            </div>
            @error('target_amount') <p class="text-sm text-red-700 mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Tanggal Mulai</label>
                <input type="date" wire:model="starts_on" class="w-full rounded border border-frost-gray px-3 py-2">
                @error('starts_on') <p class="text-sm text-red-700 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Tanggal Selesai</label>
                <input type="date" wire:model="ends_on" class="w-full rounded border border-frost-gray px-3 py-2">
                @error('ends_on') <p class="text-sm text-red-700 mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <button type="submit" class="rounded-full bg-coral-pulse px-6 py-3 text-white text-sm font-medium transition-all duration-200 ease-out hover:scale-[1.03] hover:bg-coral-pulse-dark active:scale-95">Simpan</button>
    </form>
</div>
