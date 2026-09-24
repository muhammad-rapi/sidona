<div>
    <a href="{{ route('program.index') }}" wire:navigate class="inline-flex items-center gap-1 text-sm text-graphite transition-colors duration-200 hover:text-coral-pulse">
        &larr; Kembali ke Program Donasi
    </a>

    @php
        $raised = $campaign->verifiedDonationsTotal();
        $percent = $campaign->target_amount > 0 ? min(100, (int) round($raised / $campaign->target_amount * 100)) : 0;
    @endphp

    <div class="mt-4 grid gap-8 lg:grid-cols-[1fr_380px] lg:items-start">
        <div>
            <h1 class="text-2xl font-semibold text-ink-black">{{ $campaign->name }}</h1>
            <p class="text-sm text-graphite mt-3 leading-relaxed">{{ $campaign->description }}</p>

            <div class="mt-6 bg-white rounded-3xl border border-frost-gray p-6">
                <div class="h-2 rounded-full bg-mint-wash overflow-hidden">
                    <div class="h-full rounded-full bg-coral-pulse" style="width: {{ $percent }}%"></div>
                </div>
                <div class="mt-3 flex items-baseline justify-between text-sm">
                    <span class="font-semibold text-lg text-canopy-green">Rp {{ number_format($raised, 0, ',', '.') }}</span>
                    <span class="text-graphite">terkumpul dari target Rp {{ number_format($campaign->target_amount, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        <div class="lg:sticky lg:top-6">
            @if ($referenceCode)
                <div class="bg-white p-6 rounded-3xl border border-leaf-bright">
                    <p class="text-sm">Terima kasih. Donasi Anda tercatat dengan kode referensi:</p>
                    <p class="font-mono text-lg mt-2 text-canopy-green">{{ $referenceCode }}</p>
                    <p class="text-sm text-graphite mt-2">Simpan kode ini untuk memeriksa status donasi Anda.</p>
                    <a href="{{ route('donations.check') }}" wire:navigate class="mt-4 inline-block text-sm text-coral-pulse transition-colors duration-200 hover:text-coral-pulse-dark">Cek status donasi &rarr;</a>
                </div>
            @else
                <form wire:submit="submit" class="space-y-4 bg-white p-6 rounded-3xl border border-frost-gray">
                    <h2 class="font-semibold text-ink-black">Isi Formulir Donasi</h2>

                    <div>
                        <label class="block text-sm font-medium mb-1">Nama Donatur</label>
                        <input type="text" wire:model="donor_name" class="w-full rounded border border-frost-gray px-3 py-2">
                        @error('donor_name') <p class="text-sm text-red-700 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Kontak (Telepon/Email)</label>
                        <input type="text" wire:model="donor_contact" class="w-full rounded border border-frost-gray px-3 py-2">
                        @error('donor_contact') <p class="text-sm text-red-700 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Nominal</label>
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
                        <label class="block text-sm font-medium mb-1">Bukti Transfer (jpg/png/pdf, maks 2MB)</label>
                        <input type="file" wire:model="proof" class="w-full rounded border border-frost-gray px-3 py-2">
                        @error('proof') <p class="text-sm text-red-700 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <button type="submit" class="w-full rounded-full bg-coral-pulse px-6 py-3 text-white text-sm font-medium transition-all duration-200 ease-out hover:scale-[1.03] hover:bg-coral-pulse-dark active:scale-95">Kirim Donasi</button>
                </form>
            @endif
        </div>
    </div>
</div>
