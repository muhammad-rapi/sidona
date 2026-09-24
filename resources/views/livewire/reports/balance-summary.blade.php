<div>
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-xl font-semibold">Ringkasan Saldo Program</h1>
        <button type="button" wire:click="exportPdf" class="rounded-full bg-coral px-6 py-3 text-white text-sm font-medium transition-colors duration-200 hover:bg-coral-dark">Unduh PDF</button>
    </div>

    <table class="w-full text-sm">
        <thead>
            <tr>
                <th class="border-b-2 border-ink px-4 py-3 text-left font-medium text-ink-muted">Program</th>
                <th class="border-b-2 border-ink px-4 py-3 text-left font-medium text-ink-muted">Donasi Terverifikasi</th>
                <th class="border-b-2 border-ink px-4 py-3 text-left font-medium text-ink-muted">Penyaluran Disetujui</th>
                <th class="border-b-2 border-ink px-4 py-3 text-left font-medium text-ink-muted">Saldo Tersedia</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($campaigns as $campaign)
                <tr>
                    <td class="border-b border-line px-4 py-3">{{ $campaign->name }}</td>
                    <td class="border-b border-line px-4 py-3 font-mono">Rp {{ number_format($campaign->verifiedDonationsTotal(), 0, ',', '.') }}</td>
                    <td class="border-b border-line px-4 py-3 font-mono">Rp {{ number_format($campaign->approvedDisbursementsTotal(), 0, ',', '.') }}</td>
                    <td class="border-b border-line px-4 py-3 font-mono">Rp {{ number_format($campaign->availableBalance(), 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="border-b border-line px-4 py-6 text-center text-ink-muted">Belum ada program donasi.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
