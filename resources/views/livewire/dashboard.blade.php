<div>
    <div class="page-head">
        <div>
            <h1 class="page-title">Ringkasan</h1>
            <p class="page-sub">Posisi dana hari ini. Donasi masuk otomatis; yang perlu keputusan manusia hanya penyaluran.</p>
        </div>
        @if (auth()->user()->isBendahara() || auth()->user()->isAdmin())
            <a href="{{ route('campaigns.create') }}" wire:navigate class="btn btn-paint">Program baru</a>
        @endif
    </div>

    <div class="panel mb-8 grid divide-y divide-rule md:grid-cols-3 md:divide-x md:divide-y-0">
        <div class="p-5">
            <p class="stat-label">Donasi masuk</p>
            <p class="stat-value">Rp&nbsp;{{ number_format($totalIn, 0, ',', '.') }}</p>
            <p class="mt-2 text-sm text-ink-soft">Hari ini: Rp&nbsp;{{ number_format($todayTotal, 0, ',', '.') }} dari {{ $todayCount }} donasi</p>
        </div>
        <div class="p-5">
            <p class="stat-label">Sudah disalurkan</p>
            <p class="stat-value">Rp&nbsp;{{ number_format($totalOut, 0, ',', '.') }}</p>
            <p class="mt-2 text-sm text-ink-soft">Penyaluran yang disetujui admin</p>
        </div>
        <div class="p-5">
            <p class="stat-label">Saldo tersedia</p>
            <p class="stat-value text-paid">Rp&nbsp;{{ number_format($balance, 0, ',', '.') }}</p>
            <p class="mt-2 text-sm text-ink-soft">{{ $activeCampaigns }} program aktif</p>
        </div>
    </div>

    @if ($pendingDisbursements > 0)
        <a href="{{ route('disbursements.index') }}" wire:navigate class="mb-8 flex items-center justify-between gap-4 border-2 border-ink bg-board px-5 py-4 transition-colors hover:bg-board-deep">
            <p class="font-bold">{{ $pendingDisbursements }} pengajuan penyaluran menunggu keputusan</p>
            <span class="text-sm font-bold uppercase tracking-wider">Tinjau</span>
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
