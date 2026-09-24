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
            <label class="block text-sm font-medium mb-1">Target Dana (Rupiah)</label>
            <input type="number" wire:model="target_amount" class="w-full rounded border border-frost-gray px-3 py-2">
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
