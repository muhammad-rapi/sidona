<div>
    <a href="{{ route('program.index') }}" wire:navigate class="group inline-flex items-center gap-2 rounded-full py-2 pl-1 pr-4 text-sm text-graphite transition-all duration-500 [transition-timing-function:cubic-bezier(0.32,0.72,0,1)] hover:bg-black/[0.03] hover:text-coral-pulse">
        <span class="flex h-7 w-7 items-center justify-center rounded-full bg-black/[0.04] transition-transform duration-500 [transition-timing-function:cubic-bezier(0.32,0.72,0,1)] group-hover:-translate-x-0.5">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
        </span>
        Kembali ke Program Donasi
    </a>

    @php
        $raised = $campaign->verifiedDonationsTotal();
        $percent = $campaign->target_amount > 0 ? min(100, (int) round($raised / $campaign->target_amount * 100)) : 0;
    @endphp

    <div class="mt-8 grid gap-12 py-4 lg:grid-cols-[1fr_400px] lg:items-start">
        <div class="animate-fade-in-up-blur max-w-lg">
            <span class="inline-flex items-center rounded-full bg-mint-wash px-3 py-1 text-[10px] font-semibold uppercase tracking-[0.2em] text-canopy-green">Program Aktif</span>

            <h1 class="mt-5 text-4xl font-semibold tracking-tight text-ink-black sm:text-5xl">{{ $campaign->name }}</h1>
            <p class="mt-5 text-base leading-relaxed text-graphite">{{ $campaign->description }}</p>

            <div class="mt-10 rounded-[2rem] bg-mint-wash/60 p-1.5 ring-1 ring-black/5">
                <div class="rounded-[calc(2rem-0.375rem)] bg-white p-7 shadow-[inset_0_1px_1px_rgba(255,255,255,0.9)]">
                    <div class="h-2 overflow-hidden rounded-full bg-mint-wash">
                        <div class="h-full rounded-full bg-coral-pulse transition-all duration-700 [transition-timing-function:cubic-bezier(0.32,0.72,0,1)]" style="width: {{ $percent }}%"></div>
                    </div>
                    <div class="mt-5 flex items-baseline justify-between">
                        <span class="text-2xl font-semibold tracking-tight text-canopy-green">Rp {{ number_format($raised, 0, ',', '.') }}</span>
                        <span class="text-sm text-graphite">dari target Rp {{ number_format($campaign->target_amount, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            @if ($campaign->bank_name && $campaign->account_number)
                <div class="mt-6 animate-fade-in-up-blur rounded-[2rem] bg-mint-wash/60 p-1.5 ring-1 ring-black/5" style="animation-delay: 90ms">
                    <div class="rounded-[calc(2rem-0.375rem)] bg-white p-7 shadow-[inset_0_1px_1px_rgba(255,255,255,0.9)]">
                        <p class="text-[10px] font-semibold uppercase tracking-[0.2em] text-canopy-green">Langkah Donasi</p>
                        <ol class="mt-3 list-inside list-decimal space-y-1.5 text-sm text-graphite">
                            <li>Transfer sesuai nominal ke rekening tujuan di bawah ini.</li>
                            <li>Isi formulir &amp; unggah bukti transfer di sebelah kanan.</li>
                        </ol>

                        <div class="mt-5 rounded-[1.5rem] bg-mint-wash/50 p-4 ring-1 ring-black/5">
                            <p class="text-xs uppercase tracking-wide text-graphite">{{ $campaign->bank_name }}</p>
                            <div
                                x-data="{ copied: false }"
                                x-on:click="navigator.clipboard.writeText('{{ $campaign->account_number }}'); copied = true; setTimeout(() => copied = false, 1500)"
                                class="group mt-1 flex cursor-pointer items-center justify-between gap-2"
                            >
                                <span class="font-mono text-lg font-semibold tracking-wider text-ink-black">{{ $campaign->account_number }}</span>
                                <span class="flex shrink-0 items-center justify-center rounded-full bg-white px-3 py-1 text-xs font-medium text-canopy-green shadow-[inset_0_1px_1px_rgba(255,255,255,0.9)] transition-transform duration-500 [transition-timing-function:cubic-bezier(0.32,0.72,0,1)] group-hover:scale-105" x-text="copied ? 'Tersalin!' : 'Salin'"></span>
                            </div>
                            <p class="mt-1 text-sm text-graphite">a.n. {{ $campaign->account_holder }}</p>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <div class="animate-fade-in-up-blur lg:sticky lg:top-8" style="animation-delay: 140ms">
            @if ($referenceCode)
                <div class="rounded-[2rem] bg-mint-wash/60 p-1.5 ring-1 ring-black/5">
                    <div class="rounded-[calc(2rem-0.375rem)] bg-white p-7 shadow-[inset_0_1px_1px_rgba(255,255,255,0.9)]">
                        <div class="flex h-11 w-11 items-center justify-center rounded-full bg-mint-wash text-canopy-green">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                        </div>
                        <p class="mt-4 text-sm font-medium text-ink-black">Donasi Anda berhasil tercatat</p>

                        <p class="mt-5 text-[10px] font-semibold uppercase tracking-[0.2em] text-graphite">Kode Referensi</p>
                        <div
                            x-data="{ copied: false }"
                            x-on:click="navigator.clipboard.writeText('{{ $referenceCode }}'); copied = true; setTimeout(() => copied = false, 1500)"
                            class="mt-2 flex cursor-pointer items-center justify-between gap-2 rounded-[1.5rem] border-2 border-dashed border-frost-gray bg-mint-wash/50 px-4 py-3 transition-colors duration-500 [transition-timing-function:cubic-bezier(0.32,0.72,0,1)] hover:bg-mint-wash"
                        >
                            <span class="font-mono text-xl font-semibold tracking-wider text-canopy-green">{{ $referenceCode }}</span>
                            <span class="shrink-0 text-xs font-medium text-canopy-green" x-text="copied ? 'Tersalin!' : 'Salin'"></span>
                        </div>

                        <p class="mt-4 text-sm text-graphite">Simpan kode ini untuk memeriksa status donasi Anda.</p>
                        <a href="{{ route('donations.check') }}" wire:navigate class="group mt-5 inline-flex items-center gap-2 text-sm font-medium text-coral-pulse transition-colors duration-300 hover:text-coral-pulse-dark">
                            Cek status donasi
                            <span class="flex h-6 w-6 items-center justify-center rounded-full bg-mint-wash transition-transform duration-500 [transition-timing-function:cubic-bezier(0.32,0.72,0,1)] group-hover:translate-x-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
                            </span>
                        </a>
                    </div>
                </div>
            @else
                <form wire:submit="submit" class="rounded-[2rem] bg-mint-wash/60 p-1.5 ring-1 ring-black/5">
                    <div class="space-y-5 rounded-[calc(2rem-0.375rem)] bg-white p-7 shadow-[inset_0_1px_1px_rgba(255,255,255,0.9)]">
                        <h2 class="text-lg font-semibold tracking-tight text-ink-black">Isi Formulir Donasi</h2>

                        <div>
                            <label class="mb-1.5 block text-xs font-medium uppercase tracking-wide text-graphite">Nama Donatur</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-graphite">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                </span>
                                <input type="text" wire:model="donor_name" class="w-full rounded-2xl border border-frost-gray bg-white py-2.5 pl-10 pr-4 text-sm outline-none transition-all duration-300 focus:border-coral-pulse focus:ring-2 focus:ring-coral-pulse/20">
                            </div>
                            @error('donor_name') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="mb-1.5 block text-xs font-medium uppercase tracking-wide text-graphite">Kontak (Telepon/Email)</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-graphite">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.362 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.338 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                                </span>
                                <input type="text" wire:model="donor_contact" class="w-full rounded-2xl border border-frost-gray bg-white py-2.5 pl-10 pr-4 text-sm outline-none transition-all duration-300 focus:border-coral-pulse focus:ring-2 focus:ring-coral-pulse/20">
                            </div>
                            @error('donor_contact') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="mb-1.5 block text-xs font-medium uppercase tracking-wide text-graphite">Nominal</label>
                            <div
                                x-data="{
                                    raw: $wire.entangle('amount'),
                                    formatted: '',
                                    format(v) { return v ? new Intl.NumberFormat('id-ID').format(v) : ''; },
                                    init() { this.formatted = this.format(this.raw); },
                                }"
                                class="relative"
                            >
                                <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-sm text-graphite">Rp</span>
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
                                    class="w-full rounded-2xl border border-frost-gray bg-white py-2.5 pl-10 pr-4 text-sm outline-none transition-all duration-300 focus:border-coral-pulse focus:ring-2 focus:ring-coral-pulse/20"
                                >
                            </div>
                            @error('amount') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="mb-1.5 block text-xs font-medium uppercase tracking-wide text-graphite">Waktu Transfer</label>
                            <input type="datetime-local" wire:model="transferred_at" class="w-full rounded-2xl border border-frost-gray bg-white px-4 py-2.5 text-sm outline-none transition-all duration-300 focus:border-coral-pulse focus:ring-2 focus:ring-coral-pulse/20">
                            <p class="mt-1.5 text-xs text-graphite">Isi sesuai waktu transfer di aplikasi bank/e-wallet Anda, agar mudah dicocokkan.</p>
                            @error('transferred_at') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="mb-1.5 block text-xs font-medium uppercase tracking-wide text-graphite">Bukti Transfer (jpg/png/pdf, maks 2MB)</label>
                            <input
                                type="file"
                                wire:model="proof"
                                class="w-full rounded-2xl border border-frost-gray bg-white px-4 py-2.5 text-sm outline-none transition-all duration-300 focus:border-coral-pulse focus:ring-2 focus:ring-coral-pulse/20 file:mr-3 file:rounded-full file:border-0 file:bg-mint-wash file:px-4 file:py-1.5 file:text-sm file:font-medium file:text-canopy-green file:transition-colors file:duration-300 hover:file:bg-sky-wash"
                            >
                            @error('proof') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror
                        </div>

                        <button type="submit" class="group relative flex w-full items-center justify-center gap-2 rounded-full bg-coral-pulse py-3.5 text-sm font-medium text-white transition-all duration-500 [transition-timing-function:cubic-bezier(0.32,0.72,0,1)] hover:scale-[1.02] hover:bg-coral-pulse-dark active:scale-[0.98]">
                            Kirim Donasi
                            <span class="flex h-6 w-6 items-center justify-center rounded-full bg-white/15 transition-transform duration-500 [transition-timing-function:cubic-bezier(0.32,0.72,0,1)] group-hover:translate-x-1 group-hover:-translate-y-px">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
                            </span>
                        </button>
                    </div>
                </form>
            @endif
        </div>
    </div>
</div>
