<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Donasi Terverifikasi</title>
</head>
<body style="margin:0; padding:0; background-color:#ffffff; font-family: Arial, Helvetica, sans-serif; color:#1b1b1b;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:480px; margin:0 auto; padding:32px 24px;">
        <tr>
            <td>
                <p style="font-size:12px; font-weight:bold; letter-spacing:2px; text-transform:uppercase; color:#1b5e20; margin:0 0 16px;">SIDONA</p>

                <table role="presentation" cellpadding="0" cellspacing="0" style="margin-bottom:16px;">
                    <tr>
                        <td style="width:40px; height:40px; border-radius:9999px; background-color:#e8f5e9; text-align:center; vertical-align:middle; font-size:20px; color:#1b5e20;">&#10003;</td>
                    </tr>
                </table>

                <h1 style="font-size:20px; margin:0 0 8px; color:#1b1b1b;">Donasi Anda Telah Terverifikasi</h1>
                <p style="font-size:14px; line-height:1.6; color:#3d3d3d; margin:0 0 24px;">
                    Terima kasih, {{ $donation->donor_name }}. Donasi Anda untuk program
                    <strong>{{ $donation->campaign->name }}</strong> sebesar
                    <strong>Rp {{ number_format($donation->amount, 0, ',', '.') }}</strong>
                    telah diverifikasi oleh tim kami.
                </p>

                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#e8f5e9; border-radius:16px; margin-bottom:24px;">
                    <tr>
                        <td style="padding:16px 20px;">
                            <p style="font-size:11px; text-transform:uppercase; letter-spacing:1px; color:#3d3d3d; margin:0 0 4px;">Kode Referensi</p>
                            <p style="font-family: monospace; font-size:18px; font-weight:bold; color:#1b5e20; margin:0;">{{ $donation->reference_code }}</p>
                        </td>
                    </tr>
                </table>

                <p style="font-size:13px; color:#3d3d3d; line-height:1.6;">
                    Simpan kode referensi ini untuk memeriksa status donasi Anda kapan saja di
                    <a href="{{ route('donations.check') }}" style="color:#1b5e20;">{{ route('donations.check') }}</a>.
                </p>

                <p style="font-size:12px; color:#3d3d3d; margin-top:32px;">Email ini dikirim otomatis oleh Sistem Informasi Donasi dan Audit (SIDONA).</p>
            </td>
        </tr>
    </table>
</body>
</html>
