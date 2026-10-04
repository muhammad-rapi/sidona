<!DOCTYPE html>
<html lang="id">
<head><meta charset="utf-8"><title>Pengajuan program</title></head>
<body style="margin:0; padding:0; background-color:#ffc61a; font-family: Arial, Helvetica, sans-serif; color:#17130a;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:520px; margin:0 auto;">
        <tr><td style="padding:28px 20px 8px;">
            <p style="font-size:13px; font-weight:bold; letter-spacing:3px; text-transform:uppercase; margin:0;">SIDONA</p>
            @if ($campaign->status->value === 'active')
                <h1 style="font-size:32px; line-height:1.05; margin:14px 0 0; text-transform:uppercase;">Program Anda sudah tayang.</h1>
            @else
                <h1 style="font-size:32px; line-height:1.05; margin:14px 0 0; text-transform:uppercase;">Pengajuan belum bisa kami setujui.</h1>
            @endif
        </td></tr>
        <tr><td style="padding:16px 20px 28px;">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#ffffff; border:3px solid #17130a;">
                <tr><td style="padding:22px; font-size:15px; line-height:1.6;">
                    <p style="margin:0 0 14px;">Halo {{ $campaign->proposer_name }},</p>
                    @if ($campaign->status->value === 'active')
                        <p style="margin:0 0 14px;">Program <strong>{{ $campaign->name }}</strong> sudah disetujui dan sekarang bisa menerima donasi sampai {{ $campaign->ends_on->translatedFormat('j F Y') }}.</p>
                        <a href="{{ route('program.show', $campaign) }}" style="display:inline-block; background-color:#17130a; color:#ffc61a; font-weight:bold; font-size:14px; text-decoration:none; padding:14px 22px;">Lihat program</a>
                    @else
                        <p style="margin:0 0 14px;">Program <strong>{{ $campaign->name }}</strong> belum bisa kami tayangkan.</p>
                        <p style="margin:0 0 14px;"><strong>Alasan:</strong> {{ $campaign->rejection_reason }}</p>
                        <p style="margin:0;">Anda boleh mengajukan lagi setelah data diperbaiki.</p>
                    @endif
                    <p style="margin:18px 0 0; font-size:12px; color:#5c5340;">Kode pengajuan: <span style="font-family:'Courier New', monospace; font-weight:bold;">{{ $campaign->proposal_code }}</span></p>
                </td></tr>
            </table>
        </td></tr>
    </table>
</body>
</html>
