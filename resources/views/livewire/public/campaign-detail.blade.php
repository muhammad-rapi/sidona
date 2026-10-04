@php
    $percent = $campaign->progressPercent($raised);
    $methods = \App\Enums\PaymentMethod::cases();
@endphp

<div>
    <section class="on-board bg-board" aria-labelledby="campaign-title">
        <div class="mx-auto max-w-6xl px-5 pb-10 pt-6 md:pb-14">
            <a href="{{ route('program.index') }}" wire:navigate class="inline-flex min-h-11 items-center gap-2 text-sm font-bold uppercase tracking-wider text-ink hover:text-paint-dark">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="square"><path d="M19 12H5M11 6l-6 6 6 6"/></svg>
                Semua program
            </a>

            <div class="mt-4 grid grid-cols-[1fr_auto] items-center gap-x-6 gap-y-6 sm:gap-x-12">
                <div class="rise-in min-w-0">
                    <h1 id="campaign-title" class="paint-type text-[2.5rem] leading-[0.95] text-ink sm:text-6xl lg:text-7xl">{{ $campaign->name }}</h1>
                    <p class="mt-2 text-sm font-bold uppercase tracking-wider text-ink-soft">{{ $campaign->account_holder ?: 'Program SIDONA' }}</p>

                    <p class="paint-type mt-8 text-[2.2rem] leading-none text-paint sm:text-7xl">Rp&nbsp;{{ number_format($raised, 0, ',', '.') }}</p>
                    <p class="mt-2 font-semibold text-ink">terkumpul dari target Rp&nbsp;{{ number_format($campaign->target_amount, 0, ',', '.') }}</p>
                    <p class="mt-1 text-sm text-ink-soft">{{ number_format($donorCount, 0, ',', '.') }} donatur &middot; sisa {{ $campaign->daysLeft() }} hari</p>
                </div>

                <x-thermometer :percent="$percent" rise class="[--thermo-h:15rem] sm:[--thermo-h:20rem]" />
            </div>
        </div>
        <div class="stripe"></div>
    </section>

    <div class="mx-auto grid max-w-6xl gap-x-14 gap-y-10 px-5 py-10 lg:grid-cols-[1fr_25rem] lg:items-start">
        {{-- Donation form: first on phones, sticky on the right on desktop --}}
        <form wire:submit="submit" class="rise-in order-first border-2 border-ink bg-paper lg:sticky lg:top-24 lg:order-last" style="animation-delay: 120ms" novalidate>
            <div class="on-board border-b-2 border-ink bg-board px-5 py-3">
                <h2 class="paint-type text-3xl text-ink">Donasi sekarang</h2>
            </div>

            <div class="space-y-6 p-5"
                x-data="{
                    amount: $wire.entangle('amount'),
                    chips: [25000, 50000, 100000, 250000, 500000],
                    fmt(v) { return v ? new Intl.NumberFormat('id-ID').format(v) : ''; },
                }">
                <fieldset>
                    <legend class="label">Nominal donasi</legend>
                    <div class="grid grid-cols-3 gap-2">
                        <template x-for="chip in chips" :key="chip">
                            <button type="button" @click="amount = chip"
                                class="min-h-11 border-2 border-ink px-2 py-2 text-sm font-bold transition-colors"
                                :class="amount === chip ? 'bg-ink text-board' : 'bg-paper text-ink hover:bg-board-wash'"
                                :aria-pressed="amount === chip"
                                x-text="fmt(chip)"></button>
                        </template>
                    </div>
                    <div class="relative mt-2">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-base font-bold text-ink-soft">Rp</span>
                        <input type="text" inputmode="numeric" autocomplete="off" aria-label="Nominal lain"
                            :value="fmt(amount)"
                            @input="amount = parseInt($event.target.value.replace(/[^0-9]/g, '')) || 0"
                            placeholder="Nominal lain"
                            class="field pl-11 font-bold @error('amount') field-error @enderror">
                    </div>
                    @error('amount') <p class="error-text" role="alert">{{ $message }}</p> @enderror
                </fieldset>

                <div>
                    <label for="donor_name" class="label">Nama</label>
                    <input id="donor_name" type="text" wire:model.blur="donor_name" autocomplete="name" class="field @error('donor_name') field-error @enderror">
                    @error('donor_name') <p class="error-text" role="alert">{{ $message }}</p> @enderror
                    <label class="mt-3 flex cursor-pointer items-start gap-3 text-sm">
                        <input type="checkbox" wire:model="is_anonymous" class="mt-0.5 h-5 w-5 shrink-0 border-2 border-ink">
                        <span>Samarkan nama saya. Di halaman publik tampil sebagai <strong>Hamba Allah</strong>.</span>
                    </label>
                </div>

                <div>
                    <label for="donor_contact" class="label">Email atau WhatsApp</label>
                    <input id="donor_contact" type="text" wire:model.blur="donor_contact" autocomplete="email" class="field @error('donor_contact') field-error @enderror">
                    <p class="hint">Kuitansi dikirim ke email. Kode kuitansi juga muncul di layar.</p>
                    @error('donor_contact') <p class="error-text" role="alert">{{ $message }}</p> @enderror
                </div>

                <fieldset>
                    <legend class="label">Bayar lewat</legend>
                    <div class="space-y-2">
                        @foreach ($methods as $method)
                            <label class="block cursor-pointer">
                                <input type="radio" wire:model="payment_method" value="{{ $method->value }}" class="peer sr-only">
                                <span class="flex items-center justify-between gap-3 border-2 border-ink bg-paper px-4 py-3 transition-colors peer-checked:bg-board peer-focus-visible:outline peer-focus-visible:outline-[3px] peer-focus-visible:outline-paint hover:bg-board-wash peer-checked:hover:bg-board">
                                    <span>
                                        <span class="block font-bold">{{ $method->label() }}</span>
                                        <span class="block text-xs text-ink-soft">{{ $method->hint() }}</span>
                                    </span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                    @error('payment_method') <p class="error-text" role="alert">{{ $message }}</p> @enderror
                </fieldset>

                <button type="submit" class="btn btn-paint btn-lg w-full" wire:loading.attr="disabled" wire:target="submit">
                    <span wire:loading.remove wire:target="submit">Lanjut bayar<span x-show="amount >= 10000" x-cloak> &middot; Rp&nbsp;<span x-text="fmt(amount)"></span></span></span>
                    <span wire:loading wire:target="submit">Menyiapkan pembayaran&hellip;</span>
                </button>
                <p class="text-center text-xs text-ink-soft">Donasi dikonfirmasi otomatis begitu pembayaran masuk.</p>
            </div>
        </form>

        <div class="min-w-0 space-y-12">
            @if ($campaign->cover_image)
                <div class="aspect-[16/9] overflow-hidden border-2 border-ink bg-board">
                    <img src="{{ Illuminate\Support\Facades\Storage::url($campaign->cover_image) }}" alt="{{ $campaign->name }}" class="h-full w-full object-cover">
                </div>
            @endif

            <section aria-labelledby="tentang">
                <h2 id="tentang" class="paint-type text-4xl text-ink">Tentang program ini</h2>
                <p class="mt-4 max-w-prose whitespace-pre-line text-lg leading-relaxed text-ink">{{ $campaign->description }}</p>
                <p class="mt-6 max-w-prose border-t-2 border-ink pt-4 text-sm text-ink-soft">Program berjalan sampai {{ $campaign->ends_on->translatedFormat('j F Y') }}. Dana yang terkumpul disalurkan lewat pengajuan bendahara dan persetujuan admin, dan setiap penyaluran tercatat di log audit.</p>
            </section>

            @if ($campaign->photos->isNotEmpty())
                <section aria-labelledby="galeri" x-data="{ open: null }" @keydown.escape.window="open = null">
                    <h2 id="galeri" class="paint-type text-4xl text-ink">Galeri</h2>
                    <ul class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3">
                        @foreach ($campaign->photos as $photo)
                            <li>
                                <button type="button" @click="open = '{{ $photo->url() }}'" class="block aspect-[4/3] w-full overflow-hidden border-2 border-ink bg-board" aria-label="Perbesar foto {{ $loop->iteration }}">
                                    <img src="{{ $photo->url() }}" alt="{{ $photo->caption ?? $campaign->name.' foto '.$loop->iteration }}" class="h-full w-full object-cover transition-transform duration-300 hover:scale-105" loading="lazy">
                                </button>
                            </li>
                        @endforeach
                    </ul>

                    <div x-show="open" x-cloak class="on-ink fixed inset-0 z-50 flex items-center justify-center bg-ink/90 p-4" @click="open = null" role="dialog" aria-modal="true" aria-label="Foto diperbesar">
                        <img :src="open" alt="" class="max-h-full max-w-full border-4 border-board object-contain">
                        <button type="button" class="btn btn-sm absolute right-4 top-4 border-board bg-ink text-board" @click="open = null">Tutup</button>
                    </div>
                </section>
            @endif

            <section aria-labelledby="donatur">
                <h2 id="donatur" class="paint-type text-4xl text-ink">Donatur terakhir</h2>
                @if ($recentDonations->isEmpty())
                    <p class="mt-4 text-ink-soft">Belum ada donasi. Jadilah donatur pertama untuk program ini.</p>
                @else
                    <ul class="mt-4 border-t-2 border-ink">
                        @foreach ($recentDonations as $donation)
                            <li class="flex items-baseline justify-between gap-4 border-b border-rule py-3.5">
                                <div class="min-w-0">
                                    <p class="truncate font-bold">{{ $donation->publicName() }}</p>
                                    <p class="text-sm text-ink-soft">{{ ($donation->paid_at ?? $donation->created_at)->locale('id')->diffForHumans() }}</p>
                                </div>
                                <p class="shrink-0 text-lg font-extrabold text-paint-dark">Rp&nbsp;{{ number_format($donation->amount, 0, ',', '.') }}</p>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>
        </div>
    </div>
</div>
