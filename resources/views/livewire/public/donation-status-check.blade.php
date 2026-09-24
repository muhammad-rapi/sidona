<div class="max-w-md mx-auto">
    <h1 class="text-xl font-semibold mb-6 text-center">Cek Status Donasi</h1>

    <form wire:submit="check" class="space-y-4 bg-white p-6 rounded border border-frost-gray">
        <div>
            <label class="block text-sm font-medium mb-1">Kode Referensi</label>
            <input type="text" wire:model="reference_code" class="w-full rounded border border-frost-gray px-3 py-2 font-mono">
            @error('reference_code') <p class="text-sm text-red-700 mt-1">{{ $message }}</p> @enderror
        </div>

        <button type="submit" class="w-full rounded-[40px] bg-coral-pulse px-4 py-2 text-white text-sm">Cek Status</button>
    </form>

    @if ($searched)
        <div class="mt-6 bg-white p-6 rounded border border-frost-gray">
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
