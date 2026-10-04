@php
    $method = $donation->payment_method;
    $seed = crc32($donation->reference_code);
    $cells = [];
    mt_srand($seed);
    $size = 25;
    for ($y = 0; $y < $size; $y++) {
        for ($x = 0; $x < $size; $x++) {
            $inFinder = ($x < 8 && $y < 8) || ($x >= $size - 8 && $y < 8) || ($x < 8 && $y >= $size - 8);
            if (! $inFinder && mt_rand(0, 100) < 48) {
                $cells[] = [$x, $y];
            }
        }
    }
    $finders = [[0, 0], [$size - 7, 0], [0, $size - 7]];
    $vaNumber = '8808'.str_pad((string) ($donation->id * 7919 % 100000000), 8, '0', STR_PAD_LEFT).str_pad((string) ($seed % 10000), 4, '0', STR_PAD_LEFT);
@endphp

<div>
    <section class="on-board bg-board" aria-labelledby="pay-title">
        <div class="mx-auto max-w-3xl px-5 pb-10 pt-8">
            <h1 id="pay-title" class="paint-type rise-in text-5xl text-ink sm:text-7xl">Selesaikan pembayaran</h1>
            <p class="mt-3 text-lg font-semibold text-ink">{{ $donation->campaign->name }}</p>
            <p class="paint-type mt-6 text-6xl leading-none text-paint sm:text-8xl">Rp&nbsp;{{ number_format($donation->amount, 0, ',', '.') }}</p>
        </div>
        <div class="stripe"></div>
    </section>

    <div class="mx-auto max-w-3xl space-y-8 px-5 py-10">
        <div class="border-2 border-ink bg-board-wash px-5 py-4 text-sm leading-relaxed">
            <p class="font-extrabold uppercase tracking-wider">Mode simulasi</p>
            <p class="mt-1">Belum terhubung ke bank atau e-wallet sungguhan. Tombol &ldquo;Simulasikan pembayaran berhasil&rdquo; menggantikan notifikasi otomatis dari penyedia pembayaran. Tidak ada uang yang berpindah.</p>
        </div>

        <section class="border-2 border-ink" aria-labelledby="method-title">
            <div class="flex items-center justify-between gap-4 border-b-2 border-ink bg-ink px-5 py-3 text-white">
                <h2 id="method-title" class="paint-type text-2xl text-board">{{ $method?->label() ?? 'Pembayaran' }}</h2>
                <p class="text-xs font-bold uppercase tracking-wider text-white/70">{{ $donation->reference_code }}</p>
            </div>

            <div class="p-6">
                @if ($method === \App\Enums\PaymentMethod::Qris || $method === null)
                    <div class="mx-auto w-full max-w-[17rem]">
                        <svg viewBox="-2 -2 {{ $size + 4 }} {{ $size + 4 }}" class="block w-full border-2 border-ink bg-paper" shape-rendering="crispEdges" role="img" aria-label="Kode QRIS contoh untuk simulasi">
                            @foreach ($cells as [$cx, $cy])
                                <rect x="{{ $cx }}" y="{{ $cy }}" width="1" height="1" fill="#17130a"/>
                            @endforeach
                            @foreach ($finders as [$fx, $fy])
                                <rect x="{{ $fx }}" y="{{ $fy }}" width="7" height="7" fill="#17130a"/>
                                <rect x="{{ $fx + 1 }}" y="{{ $fy + 1 }}" width="5" height="5" fill="#fff"/>
                                <rect x="{{ $fx + 2 }}" y="{{ $fy + 2 }}" width="3" height="3" fill="#17130a"/>
                            @endforeach
                        </svg>
                    </div>
                    <p class="mt-4 text-center text-sm text-ink-soft">Scan dari aplikasi bank atau e-wallet apa pun. Contoh kode untuk simulasi, bukan QRIS asli.</p>
                @elseif ($method === \App\Enums\PaymentMethod::VirtualAccount)
                    <p class="label">Nomor Virtual Account</p>
                    <div x-data="{ copied: false }" class="flex flex-wrap items-center justify-between gap-3 border-2 border-ink px-4 py-3">
                        <span class="font-mono text-2xl font-bold tracking-wider">{{ trim(chunk_split($vaNumber, 4, ' ')) }}</span>
                        <button type="button" class="btn btn-line btn-sm" @click="navigator.clipboard.writeText('{{ $vaNumber }}'); copied = true; setTimeout(() => copied = false, 1500)" x-text="copied ? 'Tersalin' : 'Salin'">Salin</button>
                    </div>
                    <ol class="mt-5 list-decimal space-y-1.5 pl-5 text-sm text-ink-soft">
                        <li>Buka ATM atau mobile banking, pilih transfer ke Virtual Account.</li>
                        <li>Masukkan nomor di atas dan pastikan nominal sesuai.</li>
                        <li>Selesaikan transfer, donasi tercatat otomatis.</li>
                    </ol>
                @else
                    <ol class="list-decimal space-y-2 pl-5 text-base">
                        <li>Buka aplikasi e-wallet Anda (GoPay, OVO, DANA, atau ShopeePay).</li>
                        <li>Setujui permintaan pembayaran sebesar <strong>Rp&nbsp;{{ number_format($donation->amount, 0, ',', '.') }}</strong>.</li>
                        <li>Donasi tercatat otomatis begitu pembayaran masuk.</li>
                    </ol>
                @endif
            </div>
        </section>

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <button type="button" wire:click="simulatePayment" wire:loading.attr="disabled" class="btn btn-paint btn-lg sm:flex-1">
                <span wire:loading.remove wire:target="simulatePayment">Simulasikan pembayaran berhasil</span>
                <span wire:loading wire:target="simulatePayment">Memproses&hellip;</span>
            </button>
            <a href="{{ route('program.show', $donation->campaign) }}" wire:navigate class="btn btn-line">Ganti nominal</a>
        </div>

        <p class="text-sm text-ink-soft">Simpan kode <span class="font-mono font-bold text-ink">{{ $donation->reference_code }}</span>. Dengan kode ini kuitansi bisa dibuka lagi kapan saja lewat menu Cek Donasi.</p>
    </div>
</div>
