<div>
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-xl font-semibold">Ringkasan Saldo Program</h1>
        <button type="button" wire:click="exportPdf" class="rounded-[40px] bg-coral-pulse px-4 py-2 text-white text-sm">Unduh PDF</button>
    </div>

    <table class="w-full border border-frost-gray text-sm bg-white">
        <thead class="bg-cloud-gray">
            <tr>
                <th class="border border-frost-gray px-3 py-2 text-left">Program</th>
                <th class="border border-frost-gray px-3 py-2 text-left">Donasi Terverifikasi</th>
                <th class="border border-frost-gray px-3 py-2 text-left">Penyaluran Disetujui</th>
                <th class="border border-frost-gray px-3 py-2 text-left">Saldo Tersedia</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($campaigns as $campaign)
                <tr>
                    <td class="border border-frost-gray px-3 py-2">{{ $campaign->name }}</td>
                    <td class="border border-frost-gray px-3 py-2">Rp {{ number_format($campaign->verifiedDonationsTotal(), 0, ',', '.') }}</td>
                    <td class="border border-frost-gray px-3 py-2">Rp {{ number_format($campaign->approvedDisbursementsTotal(), 0, ',', '.') }}</td>
                    <td class="border border-frost-gray px-3 py-2">Rp {{ number_format($campaign->availableBalance(), 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="border border-frost-gray px-3 py-6 text-center text-slate-text">Belum ada program donasi.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
