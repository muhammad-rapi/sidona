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

    <div class="mb-12 grid gap-x-12 gap-y-10 xl:grid-cols-2">
        <section aria-labelledby="antrean">
            <div class="mb-3 flex items-baseline justify-between gap-4">
                <h2 id="antrean" class="text-xl font-extrabold tracking-tight">Menunggu keputusan</h2>
                <p class="text-sm text-ink-soft">{{ $pendingProposals + $pendingDisbursements }} item</p>
            </div>
            <div class="border-t-2 border-ink">
                @forelse ($queue as $item)
                    <a href="{{ $item['url'] }}" wire:navigate class="group flex items-center justify-between gap-4 border-b border-ink/15 py-3.5 hover:bg-board-wash/60">
                        <div class="min-w-0">
                            <p class="text-xs font-bold uppercase tracking-wider text-ink-soft">{{ $item['kind'] }} &middot; {{ $item['at']->locale('id')->diffForHumans() }}</p>
                            <p class="truncate font-bold group-hover:text-paint-dark">{{ $item['title'] }}</p>
                            <p class="truncate text-sm text-ink-soft">{{ $item['meta'] }}</p>
                        </div>
                        @if ($item['amount'] !== null)
                            <p class="shrink-0 text-right text-sm"><span class="block font-bold">Rp&nbsp;{{ number_format($item['amount'], 0, ',', '.') }}</span><span class="text-ink-soft">{{ $item['amount_label'] }}</span></p>
                        @endif
                    </a>
                @empty
                    <p class="py-6 text-sm text-ink-soft">Tidak ada yang menunggu. Semua sudah diputuskan.</p>
                @endforelse
            </div>
        </section>

        <section aria-labelledby="program-berjalan">
            <div class="mb-3 flex items-baseline justify-between gap-4">
                <h2 id="program-berjalan" class="text-xl font-extrabold tracking-tight">Program berjalan</h2>
                <a href="{{ route('campaigns.index') }}" wire:navigate class="text-sm font-bold underline underline-offset-4 hover:text-paint-dark">Semua program</a>
            </div>
            <div class="border-t-2 border-ink">
                @forelse ($runningCampaigns as $campaign)
                    @php
                        $raised = (int) $campaign->raised_sum;
                        $percent = $campaign->progressPercent($raised);
                    @endphp
                    <div class="border-b border-ink/15 py-3.5">
                        <div class="flex items-baseline justify-between gap-4">
                            <p class="truncate font-bold">{{ $campaign->name }}</p>
                            <p class="shrink-0 text-sm font-bold">{{ $percent }}%</p>
                        </div>
                        <div class="thermo-h mt-2 !h-3" style="--level: {{ $percent }}" role="img" aria-label="{{ $percent }} persen dari target"><i></i></div>
                        <p class="mt-1.5 text-sm text-ink-soft">Rp&nbsp;{{ number_format($raised, 0, ',', '.') }} dari Rp&nbsp;{{ number_format($campaign->target_amount, 0, ',', '.') }}@if ($campaign->picName()) &middot; PIC {{ $campaign->picName() }}@endif</p>
                    </div>
                @empty
                    <p class="py-6 text-sm text-ink-soft">Belum ada program yang berjalan.</p>
                @endforelse
            </div>
        </section>
    </div>

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
