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
    <h1>Laporan Rekap Penyaluran Dana per Program</h1>
    <p>Dicetak: {{ now()->format('d/m/Y H:i') }}</p>

    <table>
        <thead>
            <tr>
                <th>Program</th>
                <th>Jumlah</th>
                <th>Keterangan</th>
                <th>Diajukan Oleh</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($disbursements as $disbursement)
                <tr>
                    <td>{{ $disbursement->campaign->name }}</td>
                    <td>Rp {{ number_format($disbursement->amount, 0, ',', '.') }}</td>
                    <td>{{ $disbursement->description }}</td>
                    <td>{{ $disbursement->submitter->name }}</td>
                    <td>{{ $disbursement->status->label() }}</td>
                </tr>
            @empty
                <tr><td colspan="5">Tidak ada penyaluran.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        SIDONA &mdash; Sistem Informasi Donasi dan Audit<br>
        {!! $checksumFooter !!}
    </div>
</body>
</html>
