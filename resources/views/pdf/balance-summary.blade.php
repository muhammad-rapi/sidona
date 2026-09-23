<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Helvetica, Arial, sans-serif; font-size: 11px; color: #1e293b; }
        h1 { font-size: 16px; margin-bottom: 4px; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #64748b; padding: 4px 6px; text-align: left; }
        th { background-color: #e2e8f0; }
        .footer { margin-top: 24px; font-size: 9px; color: #475569; border-top: 1px solid #cbd5e1; padding-top: 8px; }
    </style>
</head>
<body>
    <h1>Ringkasan Saldo Tiap Program</h1>
    <p>Dicetak: {{ now()->format('d/m/Y H:i') }}</p>

    <table>
        <thead>
            <tr>
                <th>Program</th>
                <th>Donasi Terverifikasi</th>
                <th>Penyaluran Disetujui</th>
                <th>Saldo Tersedia</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($campaigns as $campaign)
                <tr>
                    <td>{{ $campaign->name }}</td>
                    <td>Rp {{ number_format($campaign->verifiedDonationsTotal(), 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($campaign->approvedDisbursementsTotal(), 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($campaign->availableBalance(), 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr><td colspan="4">Belum ada program donasi.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        SIDONA &mdash; Sistem Informasi Donasi dan Audit<br>
        {!! $checksumFooter !!}
    </div>
</body>
</html>
