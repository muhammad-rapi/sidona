<div class="max-w-xl">
    <h1 class="text-xl font-semibold mb-2">Ajukan Penyaluran Dana</h1>
    <p class="text-sm text-ink-muted mb-6">
        {{ $campaign->name }} — saldo tersedia Rp {{ number_format($availableBalance, 0, ',', '.') }}
    </p>

    <form wire:submit="submit" class="space-y-4 bg-white p-6 rounded border border-line">
        <div>
            <label class="block text-sm font-medium mb-1">Jumlah (Rupiah)</label>
            <input type="number" wire:model="amount" class="w-full rounded border border-line px-3 py-2">
            @error('amount') <p class="text-sm text-red-700 mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Keterangan Penggunaan</label>
            <textarea wire:model="description" class="w-full rounded border border-line px-3 py-2"></textarea>
            @error('description') <p class="text-sm text-red-700 mt-1">{{ $message }}</p> @enderror
        </div>

        <button type="submit" class="rounded-md bg-ink px-4 py-2 text-white text-sm">Ajukan</button>
    </form>
</div>
