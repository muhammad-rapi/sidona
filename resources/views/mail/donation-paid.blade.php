<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Donasi Berhasil</title>
</head>
<body style="margin:0; padding:0; background-color:#ffc61a; font-family: Arial, Helvetica, sans-serif; color:#17130a;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:520px; margin:0 auto;">
        <tr>
            <td style="padding:28px 20px 8px;">
                <p style="font-size:13px; font-weight:bold; letter-spacing:3px; text-transform:uppercase; margin:0;">SIDONA</p>
                <h1 style="font-size:34px; line-height:1.05; margin:14px 0 0; text-transform:uppercase; letter-spacing:-0.5px;">Donasi Anda berhasil.<br>Terima kasih.</h1>
            </td>
        </tr>
        <tr>
            <td style="padding:16px 20px 28px;">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#ffffff; border:3px solid #17130a;">
                    <tr>
                        <td style="padding:22px;">
                            <p style="font-size:15px; line-height:1.6; margin:0 0 18px;">
                                {{ $donation->is_anonymous ? 'Halo' : 'Halo '.$donation->donor_name }}, donasi Anda untuk program
                                <strong>{{ $donation->campaign->name }}</strong> sudah kami terima dan tercatat.
                            </p>

                            <p style="font-size:11px; text-transform:uppercase; letter-spacing:2px; margin:0 0 4px; color:#5c5340;">Jumlah donasi</p>
                            <p style="font-size:30px; font-weight:bold; margin:0 0 18px; color:#d42a1f;">Rp {{ number_format($donation->amount, 0, ',', '.') }}</p>

                            <p style="font-size:11px; text-transform:uppercase; letter-spacing:2px; margin:0 0 4px; color:#5c5340;">Nomor kuitansi</p>
                            <p style="font-family: 'Courier New', monospace; font-size:20px; font-weight:bold; margin:0 0 22px;">{{ $donation->reference_code }}</p>

                            <a href="{{ route('donations.receipt', $donation->reference_code) }}" style="display:inline-block; background-color:#17130a; color:#ffc61a; font-weight:bold; font-size:14px; text-transform:uppercase; letter-spacing:1px; text-decoration:none; padding:14px 22px;">Lihat kuitansi</a>
                        </td>
                    </tr>
                </table>
                <p style="font-size:12px; line-height:1.6; margin:18px 0 0;">Email ini dikirim otomatis oleh SIDONA. Setiap donasi dan penyaluran dana tercatat di log berantai yang dapat diperiksa auditor.</p>
            </td>
        </tr>
    </table>
</body>
</html>
