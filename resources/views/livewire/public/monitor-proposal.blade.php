@php
    $active = $campaign->status->value === 'active';
    $percent = $campaign->progressPercent($raised);
@endphp

<div class="mx-auto max-w-4xl px-5 py-14">
    <h1 class="paint-type text-5xl text-ink sm:text-6xl">Pantau program</h1>
    <p class="mt-3 text-lg font-semibold text-ink">{{ $campaign->name }}</p>
    <p class="mt-1 text-sm text-ink-soft">Halaman pribadi untuk {{ $campaign->proposer_name }}. Jangan bagikan tautan ini. <span class="font-mono">{{ $campaign->proposal_code }}</span></p>

    @unless ($active)
        <div class="mt-8 border-2 border-ink bg-board-wash p-5">
            <p class="font-bold">Status: {{ $campaign->status->label() }}</p>
            <p class="mt-1 text-sm text-ink-soft">Data donasi muncul di sini setelah program disetujui dan tayang.
                @if ($campaign->status->value === 'rejected' && $campaign->rejection_reason) Alasan ditolak: {{ $campaign->rejection_reason }} @endif
            </p>
        </div>
    @else
        <section class="mt-10" aria-label="Posisi dana">
            <p class="paint-type text-6xl leading-none text-ink sm:text-7xl">Rp&nbsp;{{ number_format($raised, 0, ',', '.') }}</p>
            <p class="mt-2 text-ink">terkumpul dari Rp&nbsp;{{ number_format($campaign->target_amount, 0, ',', '.') }} ({{ $percent }}%)</p>
            <div class="thermo-h thermo-h-rise mt-4 !h-5 max-w-xl" style="--level: {{ $percent }}" role="img" aria-label="{{ $percent }} persen"><i></i></div>
            <p class="mt-3 text-sm text-ink-soft">{{ number_format($donorCount, 0, ',', '.') }} donatur &middot; sisa {{ $campaign->daysLeft() }} hari</p>
        </section>

        <div class="mt-10 grid gap-x-12 gap-y-10 md:grid-cols-2">
            <section aria-labelledby="penyaluran">
                <h2 id="penyaluran" class="text-2xl font-extrabold tracking-tight">Penyaluran dana</h2>
                <p class="mt-1 text-sm text-ink-soft">Sudah disalurkan Rp&nbsp;{{ number_format($disbursed, 0, ',', '.') }}. Saldo yang belum disalurkan Rp&nbsp;{{ number_format($balance, 0, ',', '.') }}.</p>
                <div class="mt-3 border-t-2 border-ink">
                    @forelse ($disbursements as $item)
                        <div class="border-b border-rule py-3">
                            <div class="flex items-baseline justify-between gap-4">
                                <p class="font-bold">Rp&nbsp;{{ number_format($item->amount, 0, ',', '.') }}</p>
                                <p class="text-sm text-ink-soft">{{ $item->reviewed_at?->timezone('Asia/Jakarta')->format('d/m/Y') }}</p>
                            </div>
                            <p class="mt-1 text-sm text-ink-soft">{{ $item->description }}</p>
                        </div>
                    @empty
                        <p class="py-4 text-sm text-ink-soft">Belum ada penyaluran yang disetujui.</p>
                    @endforelse
                </div>
            </section>

            <section aria-labelledby="donatur-terbaru">
                <h2 id="donatur-terbaru" class="text-2xl font-extrabold tracking-tight">Donasi terbaru</h2>
                <div class="mt-3 border-t-2 border-ink">
                    @forelse ($recent as $donation)
                        <div class="flex items-baseline justify-between gap-4 border-b border-rule py-3">
                            <div class="min-w-0">
                                <p class="truncate font-bold">{{ $donation->publicName() }}</p>
                                <p class="text-sm text-ink-soft">{{ ($donation->paid_at ?? $donation->created_at)->locale('id')->diffForHumans() }}</p>
                            </div>
                            <p class="shrink-0 font-extrabold text-paint-dark">Rp&nbsp;{{ number_format($donation->amount, 0, ',', '.') }}</p>
                        </div>
                    @empty
                        <p class="py-4 text-sm text-ink-soft">Belum ada donasi.</p>
                    @endforelse
                </div>
            </section>
        </div>

        <p class="mt-10 text-sm text-ink-soft">Ingin mengubah teks, foto, atau masa program? Hubungi admin dengan menyebut kode <span class="font-mono font-bold text-ink">{{ $campaign->proposal_code }}</span>. <a href="{{ route('program.show', $campaign) }}" wire:navigate class="font-bold text-ink underline underline-offset-4">Lihat halaman publik</a></p>
    @endunless
</div>
