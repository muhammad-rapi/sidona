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
        .bar-row { display: table; width: 100%; margin-bottom: 4px; }
        .bar-label { display: table-cell; width: 30%; }
        .bar-track { display: table-cell; width: 70%; }
        .bar-fill { background-color: #0f172a; height: 12px; }
        .footer { margin-top: 24px; font-size: 9px; color: #475569; border-top: 1px solid #cbd5e1; padding-top: 8px; }
    </style>
</head>
<body>
    <h1>Laporan Rekap Donasi per Program</h1>
    <p>Dicetak: {{ now()->format('d/m/Y H:i') }}</p>

    <h3>Total Donasi Terverifikasi per Program</h3>
    @php($maxTotal = $totals->max('total') ?: 1)
    @foreach ($totals as $row)
        <div class="bar-row">
            <div class="bar-label">{{ $row['campaign']->name }}</div>
            <div class="bar-track">
                <div class="bar-fill" style="width: {{ max(2, ($row['total'] / $maxTotal) * 100) }}%;"></div>
                Rp {{ number_format($row['total'], 0, ',', '.') }}
            </div>
        </div>
    @endforeach

    <table>
        <thead>
            <tr>
                <th>Kode</th>
                <th>Program</th>
                <th>Donatur</th>
                <th>Nominal</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($donations as $donation)
                <tr>
                    <td>{{ $donation->reference_code }}</td>
                    <td>{{ $donation->campaign->name }}</td>
                    <td>{{ $donation->donor_name }}</td>
                    <td>Rp {{ number_format($donation->amount, 0, ',', '.') }}</td>
                    <td>{{ $donation->status->label() }}</td>
                </tr>
            @empty
                <tr><td colspan="5">Tidak ada donasi.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        SIDONA &mdash; Sistem Informasi Donasi dan Audit<br>
        {!! $checksumFooter !!}
    </div>
</body>
</html>
