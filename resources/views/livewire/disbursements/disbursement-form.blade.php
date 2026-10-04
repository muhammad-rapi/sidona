<div class="max-w-2xl">
    <div class="page-head">
        <div>
            <h1 class="page-title">Ajukan Penyaluran Dana</h1>
            <p class="page-sub">
                {{ $campaign->name }} — saldo tersedia Rp {{ number_format($availableBalance, 0, ',', '.') }}
            </p>
        </div>
    </div>

    @if ($campaign->bank_name && $campaign->account_number)
        <div class="mb-4 border border-rule bg-board-wash p-4 text-sm">
            <p class="text-ink-soft">Sumber dana</p>
            <p class="font-bold text-ink">{{ $campaign->bank_name }} <span class="font-mono">{{ $campaign->account_number }}</span> a.n. {{ $campaign->account_holder }}</p>
        </div>
    @endif

    <form wire:submit="submit" class="panel space-y-4 p-5">
        <div>
            <label class="label">Jumlah</label>
            <div
                x-data="{
                    raw: $wire.entangle('amount'),
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
            @error('amount') <p class="error-text">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="label">Keterangan Penggunaan</label>
            <textarea wire:model="description" class="field"></textarea>
            @error('description') <p class="error-text">{{ $message }}</p> @enderror
        </div>

        <button type="submit" class="btn btn-paint">Ajukan</button>
    </form>
</div>
