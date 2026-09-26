<div class="max-w-xl">
    <h1 class="text-xl font-semibold mb-2">Ajukan Penyaluran Dana</h1>
    <p class="text-sm text-graphite mb-6">
        {{ $campaign->name }} — saldo tersedia Rp {{ number_format($availableBalance, 0, ',', '.') }}
    </p>

    @if ($campaign->bank_name && $campaign->account_number)
        <div class="mb-6 rounded-2xl border border-frost-gray bg-mint-wash/40 p-4 text-sm">
            <p class="text-graphite">Sumber dana</p>
            <p class="font-medium text-ink-black">{{ $campaign->bank_name }} <span class="font-mono">{{ $campaign->account_number }}</span> a.n. {{ $campaign->account_holder }}</p>
        </div>
    @endif

    <form wire:submit="submit" class="space-y-4 bg-white p-6 rounded-2xl border border-frost-gray">
        <div>
            <label class="block text-sm font-medium mb-1">Jumlah</label>
            <div
                x-data="{
                    raw: $wire.entangle('amount'),
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
            @error('amount') <p class="text-sm text-red-700 mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Keterangan Penggunaan</label>
            <textarea wire:model="description" class="w-full rounded border border-frost-gray px-3 py-2"></textarea>
            @error('description') <p class="text-sm text-red-700 mt-1">{{ $message }}</p> @enderror
        </div>

        <button type="submit" class="rounded-full bg-coral-pulse px-6 py-3 text-white text-sm font-medium transition-all duration-200 ease-out hover:scale-[1.03] hover:bg-coral-pulse-dark active:scale-95">Ajukan</button>
    </form>
</div>
