<div>
    <div class="page-head">
        <div>
            <h1 class="page-title">Ringkasan Saldo Program</h1>
        </div>
        <div>
            <button type="button" wire:click="exportPdf" class="btn btn-paint"><x-icon name="download" />Unduh PDF</button>
        </div>
    </div>

    <div class="panel overflow-x-auto">
        <table class="ledger">
            <thead>
                <tr>
                    <th>Program</th>
                    <th class="num">Donasi Terverifikasi</th>
                    <th class="num">Penyaluran Disetujui</th>
                    <th class="num">Saldo Tersedia</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($campaigns as $campaign)
                    <tr>
                        <td>
                            {{ $campaign->name }}
                            @if ($campaign->bank_name && $campaign->account_number)
                                <p class="text-xs text-ink-soft">{{ $campaign->bank_name }} <span class="font-mono">{{ $campaign->account_number }}</span> a.n. {{ $campaign->account_holder }}</p>
                            @endif
                        </td>
                        <td class="num">Rp&nbsp;{{ number_format($campaign->verifiedDonationsTotal(), 0, ',', '.') }}</td>
                        <td class="num">Rp&nbsp;{{ number_format($campaign->approvedDisbursementsTotal(), 0, ',', '.') }}</td>
                        <td class="num font-bold">Rp&nbsp;{{ number_format($campaign->availableBalance(), 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center text-ink-soft py-6">Belum ada program donasi.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
