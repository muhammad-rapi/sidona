<div>
    <div class="page-head">
        <div>
            <h1 class="page-title">Ringkasan</h1>
            <p class="page-sub">Donasi masuk dan tercatat sendiri. Yang menunggu keputusan manusia hanya penyaluran dan pengajuan program.</p>
        </div>
        @if (auth()->user()->canManageCampaigns())
            <a href="{{ route('campaigns.create') }}" wire:navigate class="btn btn-paint"><x-icon name="plus" />Program baru</a>
        @endif
    </div>

    @php
        $outPercent = $totalIn > 0 ? min(100, (int) round($totalOut / $totalIn * 100)) : 0;
    @endphp
    <section class="mb-10" aria-labelledby="posisi-dana">
        <h2 id="posisi-dana" class="sr-only">Posisi dana</h2>
        <p class="text-sm font-semibold text-ink-soft">Saldo yang bisa disalurkan hari ini</p>
        <p class="paint-type keep mt-1 text-6xl leading-none text-ink sm:text-8xl">Rp&nbsp;{{ number_format($balance, 0, ',', '.') }}</p>

        <div class="mt-6 max-w-3xl">
            <div class="thermo-h !h-6" style="--level: {{ $outPercent }}" role="img" aria-label="{{ $outPercent }} persen dana masuk sudah disalurkan"><i class="!bg-ink"></i></div>
            <div class="mt-2 flex flex-wrap justify-between gap-x-6 gap-y-1 text-sm">
                <p><span class="font-bold">Rp&nbsp;{{ number_format($totalOut, 0, ',', '.') }}</span> sudah disalurkan ({{ $outPercent }}%)</p>
                <p class="text-ink-soft">dari Rp&nbsp;{{ number_format($totalIn, 0, ',', '.') }} yang masuk</p>
            </div>
        </div>

        <p class="mt-5 max-w-xl text-ink">
            Hari ini masuk <strong>Rp&nbsp;{{ number_format($todayTotal, 0, ',', '.') }}</strong> dari {{ $todayCount }} donasi, ke {{ $activeCampaigns }} program yang sedang berjalan.
        </p>
    </section>

    @if ($pendingProposals > 0 && auth()->user()->hasAdminPowers())
        <a href="{{ route('campaigns.index') }}" wire:navigate class="mb-4 flex items-center justify-between gap-4 border-2 border-ink bg-board px-5 py-4 transition-colors hover:bg-board-deep">
            <p class="font-bold">{{ $pendingProposals }} pengajuan program baru menunggu tinjauan</p>
            <span class="text-sm font-bold">Tinjau</span>
        </a>
    @endif

    @if ($pendingDisbursements > 0)
        <a href="{{ route('disbursements.index') }}" wire:navigate class="mb-8 flex items-center justify-between gap-4 border-2 border-ink bg-board px-5 py-4 transition-colors hover:bg-board-deep">
            <p class="font-bold">{{ $pendingDisbursements }} pengajuan penyaluran menunggu keputusan</p>
            <span class="text-sm font-bold">Tinjau</span>
        </a>
    @endif

    <section aria-labelledby="terbaru">
        <div class="mb-3 flex items-baseline justify-between gap-4">
            <h2 id="terbaru" class="paint-type text-3xl">Donasi terbaru</h2>
            <a href="{{ route('donations.index') }}" wire:navigate class="text-sm font-bold uppercase tracking-wider underline underline-offset-4 hover:text-paint-dark">Semua riwayat</a>
        </div>
        <div class="panel overflow-x-auto">
            <table class="ledger">
                <thead>
                    <tr><th>Donatur</th><th>Program</th><th class="num">Nominal</th><th>Waktu</th></tr>
                </thead>
                <tbody>
                    @forelse ($recentDonations as $donation)
                        <tr>
                            <td class="font-semibold">{{ $donation->donor_name }}</td>
                            <td>{{ $donation->campaign->name }}</td>
                            <td class="num font-bold">Rp&nbsp;{{ number_format($donation->amount, 0, ',', '.') }}</td>
                            <td class="whitespace-nowrap text-ink-soft">{{ ($donation->paid_at ?? $donation->created_at)->timezone('Asia/Jakarta')->locale('id')->diffForHumans() }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="py-10 text-center text-ink-soft">Belum ada donasi berhasil.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
