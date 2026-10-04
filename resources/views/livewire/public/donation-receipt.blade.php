@php
    $paid = $donation->isPaid();
    $failed = $donation->status === \App\Enums\DonationStatus::Rejected;
    $targetPercent = $campaign->progressPercent($raised);
    $fromPercent = $campaign->progressPercent($raisedBefore);
    $stamp = $paid ? ['LUNAS', 'border-paid text-paid'] : ($failed ? ['GAGAL', 'border-paint text-paint-dark'] : ['BELUM LUNAS', 'border-ink-soft text-ink-soft']);
    $time = $donation->paid_at ?? $donation->created_at;
@endphp

<div>
    <section class="on-board bg-board print:hidden" aria-labelledby="receipt-title">
        <div class="mx-auto grid max-w-6xl grid-cols-[1fr_auto] items-center gap-x-6 gap-y-6 px-5 py-10 sm:gap-x-12 md:py-14">
            <div class="min-w-0">
                @if ($paid)
                    <h1 id="receipt-title" class="paint-type rise-in text-[2.75rem] leading-[0.95] text-ink sm:text-6xl lg:text-7xl">Terima kasih{{ $donation->is_anonymous ? '' : ', '.rtrim(strtok($donation->donor_name, ' '), '.') }}.</h1>
                    <p class="mt-5 max-w-md text-lg font-semibold text-ink">@if ($targetPercent > $fromPercent)Dana program naik dari {{ $fromPercent }}% ke {{ $targetPercent }}% dari target. @endif Donasi Anda sudah tercatat di <em class="not-italic underline decoration-2 underline-offset-4">{{ $campaign->name }}</em>.</p>
                @elseif ($failed)
                    <h1 id="receipt-title" class="paint-type text-5xl text-ink sm:text-7xl">Donasi gagal</h1>
                    <p class="mt-5 max-w-md text-lg font-semibold text-ink">Pembayaran tidak selesai. Anda bisa mencoba berdonasi lagi kapan saja.</p>
                    <a href="{{ route('program.show', $campaign) }}" wire:navigate class="btn btn-paint btn-lg mt-6">Coba lagi</a>
                @else
                    <h1 id="receipt-title" class="paint-type text-5xl text-ink sm:text-7xl">Menunggu pembayaran</h1>
                    <p class="mt-5 max-w-md text-lg font-semibold text-ink">Kuitansi menjadi lunas otomatis begitu pembayaran masuk.</p>
                    <a href="{{ route('donations.pay', $donation->reference_code) }}" wire:navigate class="btn btn-paint btn-lg mt-6">Lanjut bayar</a>
                @endif
            </div>

            <x-thermometer :percent="$targetPercent" :from="$fromPercent" :rise="$paid" class="[--thermo-h:15rem] sm:[--thermo-h:21rem]" />
        </div>
        <div class="stripe"></div>
    </section>

    <div class="mx-auto max-w-2xl px-5 py-12 print:py-0">
        <article class="relative border-2 border-ink bg-paper print:border-0" aria-labelledby="kuitansi-title">
            <header class="flex items-start justify-between gap-4 border-b-2 border-ink px-6 py-5">
                <div>
                    <h2 id="kuitansi-title" class="paint-type text-5xl leading-none text-paint">Kuitansi</h2>
                    <p class="mt-2 text-xs font-bold uppercase tracking-wider text-ink-soft">SIDONA &middot; Sistem Informasi Donasi dan Audit</p>
                </div>
                <div class="text-right">
                    <p class="text-xs font-bold uppercase tracking-wider text-ink-soft">Nomor</p>
                    <p class="font-mono text-lg font-bold" x-data="{ copied: false }">
                        <button type="button" class="hover:text-paint-dark print:pointer-events-none" @click="navigator.clipboard.writeText('{{ $donation->reference_code }}'); copied = true; setTimeout(() => copied = false, 1500)" :title="'Salin nomor kuitansi'">
                            <span x-show="!copied">{{ $donation->reference_code }}</span>
                            <span x-show="copied" x-cloak>Tersalin</span>
                        </button>
                    </p>
                </div>
            </header>

            <dl class="divide-y divide-rule px-6">
                <div class="grid gap-1 py-4 sm:grid-cols-[10rem_1fr] sm:gap-4">
                    <dt class="text-sm font-bold uppercase tracking-wider text-ink-soft">Diterima dari</dt>
                    <dd class="font-bold">{{ $donation->is_anonymous ? 'Hamba Allah' : $donation->donor_name }}</dd>
                </div>
                <div class="grid gap-1 py-4 sm:grid-cols-[10rem_1fr] sm:gap-4">
                    <dt class="text-sm font-bold uppercase tracking-wider text-ink-soft">Uang sejumlah</dt>
                    <dd class="paint-type text-4xl leading-none text-paint">Rp&nbsp;{{ number_format($donation->amount, 0, ',', '.') }}</dd>
                </div>
                <div class="grid gap-1 py-4 sm:grid-cols-[10rem_1fr] sm:gap-4">
                    <dt class="text-sm font-bold uppercase tracking-wider text-ink-soft">Untuk program</dt>
                    <dd class="font-bold">{{ $campaign->name }}</dd>
                </div>
                <div class="grid gap-1 py-4 sm:grid-cols-[10rem_1fr] sm:gap-4">
                    <dt class="text-sm font-bold uppercase tracking-wider text-ink-soft">Pembayaran</dt>
                    <dd>{{ $donation->payment_method?->label() ?? 'Transfer' }}</dd>
                </div>
                <div class="grid gap-1 py-4 sm:grid-cols-[10rem_1fr] sm:gap-4">
                    <dt class="text-sm font-bold uppercase tracking-wider text-ink-soft">{{ $paid ? 'Waktu lunas' : 'Dibuat' }}</dt>
                    <dd>{{ $time->timezone('Asia/Jakarta')->locale('id')->translatedFormat('j F Y, H:i') }} WIB</dd>
                </div>
            </dl>

            <footer class="flex items-center justify-between gap-4 border-t-2 border-ink px-6 py-5">
                <p class="max-w-[16rem] text-xs leading-relaxed text-ink-soft">Tercatat otomatis di log berantai SIDONA. Kuitansi ini sah tanpa tanda tangan.</p>
                <p class="paint-type -rotate-6 border-4 px-3 py-1 text-3xl leading-none {{ $stamp[1] }}">{{ $stamp[0] }}</p>
            </footer>
        </article>

        <div class="mt-8 flex flex-wrap gap-3 print:hidden">
            @if ($paid)
                <button type="button" class="btn btn-ink" onclick="window.print()">Cetak kuitansi</button>
            @endif
            <a href="{{ route('program.show', $campaign) }}" wire:navigate class="btn btn-line">Lihat program</a>
            <a href="{{ route('program.index') }}" wire:navigate class="btn btn-line">Program lain</a>
        </div>
    </div>
</div>
