<div class="space-y-8">
    <h1 class="text-xl font-semibold">Dashboard Anomali</h1>

    <section>
        <h2 class="font-semibold mb-3">Donasi Nominal Ekstrem</h2>
        @forelse ($extremeDonations as $donation)
            <div class="border border-red-700 bg-red-50 rounded px-4 py-3 mb-2 text-sm">
                <span class="font-mono">{{ $donation->reference_code }}</span> —
                {{ $donation->campaign->name }} — Rp {{ number_format($donation->amount, 0, ',', '.') }}
                (jauh di atas rata rata donasi program ini)
            </div>
        @empty
            <p class="text-sm text-ink-muted">Tidak ada donasi dengan nominal mencurigakan.</p>
        @endforelse
    </section>

    <section>
        <h2 class="font-semibold mb-3">Percobaan Login Gagal Berturut Turut</h2>
        @forelse ($failedLoginStreaks as $log)
            <div class="border border-red-700 bg-red-50 rounded px-4 py-3 mb-2 text-sm">
                {{ $log->email }} — percobaan login gagal berturut turut dalam 15 menit terakhir
                ({{ $log->created_at->format('d/m/Y H:i') }})
            </div>
        @empty
            <p class="text-sm text-ink-muted">Tidak ada pola login gagal yang mencurigakan.</p>
        @endforelse
    </section>

    <section>
        <h2 class="font-semibold mb-3">Penyaluran Disetujui Terlalu Cepat</h2>
        @forelse ($fastApprovedDisbursements as $disbursement)
            <div class="border border-red-700 bg-red-50 rounded px-4 py-3 mb-2 text-sm">
                {{ $disbursement->campaign->name }} — Rp {{ number_format($disbursement->amount, 0, ',', '.') }}
                disetujui kurang dari 1 menit setelah diajukan
            </div>
        @empty
            <p class="text-sm text-ink-muted">Tidak ada penyaluran yang disetujui terlalu cepat.</p>
        @endforelse
    </section>
</div>
