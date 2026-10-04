@php
    $sections = [
        ['title' => 'Donasi Nominal Ekstrem', 'type' => 'donation', 'items' => $extremeDonations, 'empty' => 'Tidak ada donasi dengan nominal mencurigakan.'],
        ['title' => 'Percobaan Login Gagal Berturut Turut', 'type' => 'login', 'items' => $failedLoginStreaks, 'empty' => 'Tidak ada pola login gagal yang mencurigakan.'],
        ['title' => 'Penyaluran Disetujui Terlalu Cepat', 'type' => 'disbursement', 'items' => $fastApprovedDisbursements, 'empty' => 'Tidak ada penyaluran yang disetujui terlalu cepat.'],
    ];
@endphp

<div class="space-y-8">
    <div class="page-head">
        <div>
            <h1 class="page-title">Dashboard Anomali</h1>
            <p class="page-sub">Buka Detail untuk meninjau tiap temuan, lalu tandai setelah diperiksa. Penandaan tercatat di log aktivitas.</p>
        </div>
    </div>

    @foreach ($sections as $section)
        <section>
            <div class="mb-1 flex items-baseline justify-between gap-4">
                <h2 class="text-xl font-extrabold tracking-tight">{{ $section['title'] }}</h2>
                <p class="text-sm text-ink-soft">{{ $section['items']->count() }} temuan</p>
            </div>
            <div class="border-t-2 border-ink">
            @forelse ($section['items'] as $item)
                @php $review = $reviews->get($section['type'].':'.$item->id); @endphp
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-ink/15 py-3.5 text-sm {{ $review ? 'text-ink-soft' : 'text-ink' }}">
                    <p class="flex min-w-0 items-baseline gap-3">
                        <span class="mt-1 h-2.5 w-2.5 shrink-0 {{ $review ? 'bg-ink-faint' : 'bg-paint' }}" aria-hidden="true"></span>
                        <span>
                        @if ($section['type'] === 'donation')
                            <span class="font-mono">{{ $item->reference_code }}</span> —
                            {{ $item->campaign->name }} — Rp&nbsp;{{ number_format($item->amount, 0, ',', '.') }}
                            (jauh di atas rata rata donasi program ini)
                        @elseif ($section['type'] === 'login')
                            {{ $item->email }} — percobaan login gagal berturut turut dalam 15 menit terakhir
                            ({{ $item->created_at->format('d/m/Y H:i') }})
                        @else
                            {{ $item->campaign->name }} — Rp&nbsp;{{ number_format($item->amount, 0, ',', '.') }}
                            disetujui kurang dari 1 menit setelah diajukan
                        @endif
                        </span>
                    </p>

                    <div class="flex shrink-0 flex-wrap items-center gap-2">
                        @if ($review)
                            <span class="badge badge-paid">Diperiksa {{ $review->reviewer->name }}, {{ $review->reviewed_at->timezone('Asia/Jakarta')->format('d/m/Y H:i') }}</span>
                        @endif
                        <x-action :icon="$detailKey === $section['type'].':'.$item->id ? 'close' : 'detail'" :label="$detailKey === $section['type'].':'.$item->id ? 'Tutup detail' : 'Lihat detail'" wire:click="toggleDetail('{{ $section['type'] }}', {{ $item->id }})" aria-expanded="{{ $detailKey === $section['type'].':'.$item->id ? 'true' : 'false' }}" />
                    </div>
                </div>

                @if ($detailKey === $section['type'].':'.$item->id)
                    <div class="border-b border-ink/15 bg-board-wash/50 px-5 py-5 text-ink">
                        <div class="grid gap-x-10 gap-y-6 md:grid-cols-2">
                            <dl class="space-y-2 text-sm">
                                @if ($section['type'] === 'donation')
                                    <div class="flex gap-4"><dt class="w-36 shrink-0 font-bold text-ink-soft">Donatur</dt><dd>{{ $item->donor_name }}</dd></div>
                                    <div class="flex gap-4"><dt class="w-36 shrink-0 font-bold text-ink-soft">Kontak</dt><dd class="break-all">{{ $item->donor_contact }}</dd></div>
                                    <div class="flex gap-4"><dt class="w-36 shrink-0 font-bold text-ink-soft">Nominal</dt><dd class="font-bold">Rp&nbsp;{{ number_format($item->amount, 0, ',', '.') }}</dd></div>
                                    <div class="flex gap-4"><dt class="w-36 shrink-0 font-bold text-ink-soft">Rata-rata program</dt><dd>Rp&nbsp;{{ number_format($donationContext['average'] ?? 0, 0, ',', '.') }} dari {{ $donationContext['count'] ?? 0 }} donasi</dd></div>
                                    <div class="flex gap-4"><dt class="w-36 shrink-0 font-bold text-ink-soft">Kelipatan rata-rata</dt><dd>{{ ($donationContext['average'] ?? 0) > 0 ? number_format($item->amount / $donationContext['average'], 1, ',', '.') : '-' }}x</dd></div>
                                    <div class="flex gap-4"><dt class="w-36 shrink-0 font-bold text-ink-soft">Metode bayar</dt><dd>{{ $item->payment_method?->label() ?? '-' }}</dd></div>
                                    <div class="flex gap-4"><dt class="w-36 shrink-0 font-bold text-ink-soft">Status</dt><dd>{{ $item->status->label() }}</dd></div>
                                    <div class="flex gap-4"><dt class="w-36 shrink-0 font-bold text-ink-soft">Waktu</dt><dd>{{ ($item->paid_at ?? $item->created_at)->timezone('Asia/Jakarta')->format('d/m/Y H:i') }} WIB</dd></div>
                                @elseif ($section['type'] === 'login')
                                    <div class="flex gap-4"><dt class="w-36 shrink-0 font-bold text-ink-soft">Email</dt><dd>{{ $item->email }}</dd></div>
                                    <div class="flex gap-4"><dt class="w-36 shrink-0 font-bold text-ink-soft">Alamat IP</dt><dd class="font-mono">{{ $item->ip_address }}</dd></div>
                                    <div class="flex gap-4"><dt class="w-36 shrink-0 font-bold text-ink-soft">Perangkat</dt><dd>{{ $item->deviceLabel() }}</dd></div>
                                @else
                                    <div class="flex gap-4"><dt class="w-36 shrink-0 font-bold text-ink-soft">Program</dt><dd>{{ $item->campaign->name }}</dd></div>
                                    <div class="flex gap-4"><dt class="w-36 shrink-0 font-bold text-ink-soft">Jumlah</dt><dd class="font-bold">Rp&nbsp;{{ number_format($item->amount, 0, ',', '.') }}</dd></div>
                                    <div class="flex gap-4"><dt class="w-36 shrink-0 font-bold text-ink-soft">Peruntukan</dt><dd>{{ $item->description }}</dd></div>
                                    <div class="flex gap-4"><dt class="w-36 shrink-0 font-bold text-ink-soft">Diajukan oleh</dt><dd>{{ $item->submitter?->name ?? '-' }}, {{ $item->created_at->timezone('Asia/Jakarta')->format('d/m/Y H:i:s') }}</dd></div>
                                    <div class="flex gap-4"><dt class="w-36 shrink-0 font-bold text-ink-soft">Disetujui oleh</dt><dd>{{ $item->reviewer?->name ?? '-' }}, {{ $item->reviewed_at?->timezone('Asia/Jakarta')->format('d/m/Y H:i:s') }}</dd></div>
                                    <div class="flex gap-4"><dt class="w-36 shrink-0 font-bold text-ink-soft">Selisih waktu</dt><dd>{{ $item->created_at->diffInSeconds($item->reviewed_at) }} detik</dd></div>
                                @endif
                            </dl>

                            <div>
                                @if ($section['type'] === 'donation')
                                    <p class="mb-2 text-sm font-bold text-ink-soft">Jejak audit donasi ini</p>
                                    @forelse ($donationContext['trail'] ?? [] as $entry)
                                        <div class="flex items-baseline justify-between gap-4 border-b border-rule py-1.5 text-sm">
                                            <span><span class="font-mono text-xs">{{ $entry->action }}</span> oleh {{ $entry->user?->name ?? ($entry->action === 'donation.paid' ? 'Sistem (otomatis)' : 'Tamu') }}</span>
                                            <span class="shrink-0 text-ink-soft">{{ $entry->created_at->timezone('Asia/Jakarta')->format('d/m/Y H:i') }}</span>
                                        </div>
                                    @empty
                                        <p class="text-sm text-ink-soft">Belum ada catatan audit.</p>
                                    @endforelse
                                    <a href="{{ route('donations.receipt', $item->reference_code) }}" target="_blank" class="mt-3 inline-block text-sm font-bold uppercase tracking-wider underline underline-offset-4 hover:text-paint-dark">Buka kuitansi</a>
                                @elseif ($section['type'] === 'login')
                                    <p class="mb-2 text-sm font-bold text-ink-soft">Percobaan terakhir untuk email ini</p>
                                    @foreach ($loginAttempts as $attempt)
                                        <div class="flex items-baseline justify-between gap-4 border-b border-rule py-1.5 text-sm">
                                            <span><span class="badge {{ $attempt->status === 'failed' ? 'badge-fail' : 'badge-paid' }}">{{ $attempt->status === 'failed' ? 'Gagal' : 'Berhasil' }}</span> <span class="font-mono text-xs">{{ $attempt->ip_address }}</span></span>
                                            <span class="shrink-0 text-ink-soft">{{ $attempt->created_at->timezone('Asia/Jakarta')->format('d/m/Y H:i:s') }}</span>
                                        </div>
                                    @endforeach
                                @else
                                    <p class="text-sm text-ink-soft">Persetujuan kurang dari 60 detik setelah pengajuan berarti isi pengajuan kemungkinan tidak sempat ditinjau.</p>
                                @endif
                            </div>
                        </div>

                        <div class="mt-5 flex flex-wrap items-center gap-3 border-t border-rule pt-4">
                            @if ($review)
                                <p class="text-sm font-bold text-paid">Sudah diperiksa oleh {{ $review->reviewer->name }}.</p>
                            @else
                                <button type="button" wire:click="markChecked('{{ $section['type'] }}', {{ $item->id }})" wire:loading.attr="disabled" class="btn btn-ink btn-sm">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="square" aria-hidden="true"><path d="M5 12.5 10 17.5 19 7"/></svg>
                                    Tandai diperiksa
                                </button>
                                <p class="text-sm text-ink-soft">Tandai setelah Anda meninjau rincian di atas.</p>
                            @endif
                        </div>
                    </div>
                @endif
            @empty
                <p class="py-5 text-sm text-ink-soft">{{ $section['empty'] }}</p>
            @endforelse
            </div>
        </section>
    @endforeach
</div>
