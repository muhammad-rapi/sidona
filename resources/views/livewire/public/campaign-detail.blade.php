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

            @if ($campaign->bank_name && $campaign->account_number)
                <div class="mt-6 bg-mint-wash rounded-3xl border border-frost-gray p-6">
                    <p class="text-sm font-medium text-canopy-green">Langkah Donasi</p>
                    <ol class="mt-2 text-sm text-graphite list-decimal list-inside space-y-1">
                        <li>Transfer sesuai nominal ke rekening tujuan di bawah ini.</li>
                        <li>Isi formulir &amp; unggah bukti transfer di sebelah kanan.</li>
                    </ol>

                    <div class="mt-4 bg-white rounded-2xl border border-frost-gray p-4">
                        <p class="text-xs text-graphite uppercase tracking-wide">{{ $campaign->bank_name }}</p>
                        <div
                            x-data="{ copied: false }"
                            x-on:click="navigator.clipboard.writeText('{{ $campaign->account_number }}'); copied = true; setTimeout(() => copied = false, 1500)"
                            class="mt-1 flex items-center justify-between gap-2 cursor-pointer"
                        >
                            <span class="font-mono text-lg font-semibold tracking-wider text-ink-black">{{ $campaign->account_number }}</span>
                            <span class="shrink-0 text-xs font-medium text-canopy-green" x-text="copied ? 'Tersalin!' : 'Salin'"></span>
                        </div>
                        <p class="text-sm text-graphite mt-1">a.n. {{ $campaign->account_holder }}</p>
                    </div>
                </div>
            @endif
        </div>

        <div class="lg:sticky lg:top-6">
            @if ($referenceCode)
                <div class="bg-white p-6 rounded-3xl border border-leaf-bright animate-fade-in-up">
                    <div class="flex items-center gap-2 text-canopy-green">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/></svg>
                        <p class="text-sm font-medium">Donasi Anda berhasil tercatat</p>
                    </div>

                    <p class="text-xs text-graphite mt-4 uppercase tracking-wide">Kode Referensi</p>
                    <div
                        x-data="{ copied: false }"
                        x-on:click="navigator.clipboard.writeText('{{ $referenceCode }}'); copied = true; setTimeout(() => copied = false, 1500)"
                        class="mt-1 flex items-center justify-between gap-2 rounded-2xl border-2 border-dashed border-frost-gray bg-mint-wash px-4 py-3 cursor-pointer transition-colors duration-200 hover:bg-sky-wash"
                    >
                        <span class="font-mono text-xl font-semibold tracking-wider text-canopy-green">{{ $referenceCode }}</span>
                        <span class="shrink-0 text-xs font-medium text-canopy-green" x-text="copied ? 'Tersalin!' : 'Salin'"></span>
                    </div>

                    <p class="text-sm text-graphite mt-3">Simpan kode ini untuk memeriksa status donasi Anda.</p>
                    <a href="{{ route('donations.check') }}" wire:navigate class="mt-4 inline-block text-sm text-coral-pulse transition-colors duration-200 hover:text-coral-pulse-dark">Cek status donasi &rarr;</a>
                </div>
            @else
                <form wire:submit="submit" class="space-y-4 bg-white p-6 rounded-3xl border border-frost-gray">
                    <h2 class="font-semibold text-ink-black animate-fade-in-up" style="animation-delay: 0ms">Isi Formulir Donasi</h2>

                    <div class="animate-fade-in-up" style="animation-delay: 60ms">
                        <label class="block text-sm font-medium mb-1">Nama Donatur</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-graphite">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                            </span>
                            <input type="text" wire:model="donor_name" class="w-full rounded border border-frost-gray pl-9 pr-3 py-2 transition-all duration-200 outline-none focus:border-coral-pulse focus:ring-2 focus:ring-coral-pulse/20">
                        </div>
                        @error('donor_name') <p class="text-sm text-red-700 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="animate-fade-in-up" style="animation-delay: 120ms">
                        <label class="block text-sm font-medium mb-1">Kontak (Telepon/Email)</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-graphite">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.362 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.338 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                            </span>
                            <input type="text" wire:model="donor_contact" class="w-full rounded border border-frost-gray pl-9 pr-3 py-2 transition-all duration-200 outline-none focus:border-coral-pulse focus:ring-2 focus:ring-coral-pulse/20">
                        </div>
                        @error('donor_contact') <p class="text-sm text-red-700 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="animate-fade-in-up" style="animation-delay: 180ms">
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
                                class="w-full rounded border border-frost-gray pl-9 pr-3 py-2 transition-all duration-200 outline-none focus:border-coral-pulse focus:ring-2 focus:ring-coral-pulse/20"
                            >
                        </div>
                        @error('amount') <p class="text-sm text-red-700 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="animate-fade-in-up" style="animation-delay: 240ms">
                        <label class="block text-sm font-medium mb-1">Bukti Transfer (jpg/png/pdf, maks 2MB)</label>
                        <input
                            type="file"
                            wire:model="proof"
                            class="w-full rounded border border-frost-gray px-3 py-2 text-sm transition-all duration-200 outline-none focus:border-coral-pulse focus:ring-2 focus:ring-coral-pulse/20 file:mr-3 file:rounded-full file:border-0 file:bg-mint-wash file:px-4 file:py-1.5 file:text-sm file:font-medium file:text-canopy-green file:transition-colors file:duration-200 hover:file:bg-sky-wash"
                        >
                        @error('proof') <p class="text-sm text-red-700 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <button type="submit" class="w-full rounded-full bg-coral-pulse px-6 py-3 text-white text-sm font-medium transition-all duration-200 ease-out animate-fade-in-up hover:scale-[1.03] hover:bg-coral-pulse-dark active:scale-95" style="animation-delay: 300ms">Kirim Donasi</button>
                </form>
            @endif
        </div>
    </div>
</div>
