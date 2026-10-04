<!DOCTYPE html>
<html lang="id">
<head><meta charset="utf-8"><title>Konfirmasi email</title></head>
<body style="margin:0; padding:0; background-color:#ffc61a; font-family: Arial, Helvetica, sans-serif; color:#17130a;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:520px; margin:0 auto;">
        <tr><td style="padding:28px 20px 8px;">
            <p style="font-size:13px; font-weight:bold; letter-spacing:3px; text-transform:uppercase; margin:0;">SIDONA</p>
            <h1 style="font-size:32px; line-height:1.05; margin:14px 0 0; text-transform:uppercase;">Satu langkah lagi.</h1>
        </td></tr>
        <tr><td style="padding:16px 20px 28px;">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#ffffff; border:3px solid #17130a;">
                <tr><td style="padding:22px; font-size:15px; line-height:1.6;">
                    <p style="margin:0 0 14px;">Halo {{ $campaign->proposer_name }}, pengajuan program <strong>{{ $campaign->name }}</strong> sudah kami terima. Tekan tombol di bawah untuk memastikan email ini milik Anda. Admin baru bisa menyetujui setelah email terkonfirmasi.</p>
                    <a href="{{ $url }}" style="display:inline-block; background-color:#17130a; color:#ffc61a; font-weight:bold; font-size:14px; text-decoration:none; padding:14px 22px;">Konfirmasi email</a>
                    <p style="margin:18px 0 0; font-size:12px; color:#5c5340;">Kode pengajuan: <span style="font-family:'Courier New', monospace; font-weight:bold;">{{ $campaign->proposal_code }}</span>. Tautan berlaku 2 hari.</p>
                </td></tr>
            </table>
        </td></tr>
    </table>
</body>
</html>
