<div class="max-w-md mx-auto">
    <h1 class="text-xl font-semibold mb-6 text-center">Cek Status Donasi</h1>

    <form wire:submit="check" class="space-y-4 bg-white p-6 rounded-2xl border border-frost-gray">
        <div>
            <label class="block text-sm font-medium mb-1">Kode Referensi</label>
            <input type="text" wire:model="reference_code" class="w-full rounded border border-frost-gray px-3 py-2 font-mono">
            @error('reference_code') <p class="text-sm text-red-700 mt-1">{{ $message }}</p> @enderror
        </div>

        <button type="submit" class="w-full rounded-full bg-coral-pulse px-6 py-3 text-white text-sm font-medium transition-all duration-200 ease-out hover:scale-[1.03] hover:bg-coral-pulse-dark active:scale-95">Cek Status</button>
    </form>

    @if ($searched)
        <div class="mt-6 bg-white p-6 rounded-2xl border border-frost-gray">
            @if ($result)
                <p class="text-sm">Kode: <span class="font-mono">{{ $result->reference_code }}</span></p>
                <p class="text-sm mt-1">Program: {{ $result->campaign->name }}</p>
                <p class="text-sm mt-1">Nominal: Rp {{ number_format($result->amount, 0, ',', '.') }}</p>
                <p class="text-sm mt-1">Status: {{ $result->status->label() }}</p>
                @if ($result->rejection_reason)
                    <p class="text-sm mt-1">Alasan: {{ $result->rejection_reason }}</p>
                @endif
            @else
                <p class="text-sm text-graphite">Kode referensi tidak ditemukan.</p>
            @endif
        </div>
    @endif
</div>
