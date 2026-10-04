<!DOCTYPE html>
<html lang="id">
<head><meta charset="utf-8"><title>Tiket bantuan</title></head>
<body style="margin:0; padding:0; background-color:#ffc61a; font-family: Arial, Helvetica, sans-serif; color:#17130a;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:520px; margin:0 auto;">
        <tr><td style="padding:28px 20px 8px;">
            <p style="font-size:13px; font-weight:bold; letter-spacing:3px; text-transform:uppercase; margin:0;">SIDONA</p>
            <h1 style="font-size:30px; line-height:1.05; margin:14px 0 0; text-transform:uppercase;">{{ match ($kind) { 'reply' => 'Ada balasan untuk Anda.', 'link' => 'Ini tautan tiket Anda.', default => 'Pesan Anda sudah kami terima.' } }}</h1>
        </td></tr>
        <tr><td style="padding:16px 20px 28px;">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#ffffff; border:3px solid #17130a;">
                <tr><td style="padding:22px; font-size:15px; line-height:1.6;">
                    <p style="margin:0 0 12px;">Halo {{ $ticket->name }},</p>
                    @if ($kind === 'reply' && $reply)
                        <p style="margin:0 0 12px;"><strong>{{ $reply->author_name }}</strong> membalas tiket <strong>{{ $ticket->subject }}</strong>:</p>
                        <p style="margin:0 0 16px; padding:12px; background-color:#fff0b8; white-space:pre-line;">{{ $reply->body }}</p>
                    @elseif ($kind === 'link')
                        <p style="margin:0 0 16px;">Anda meminta tautan untuk tiket <strong>{{ $ticket->subject }}</strong>. Kalau bukan Anda, abaikan email ini.</p>
                    @else
                        <p style="margin:0 0 16px;">Tiket <strong>{{ $ticket->subject }}</strong> sudah masuk. Tim kami akan membalas lewat email ini.</p>
                    @endif
                    <a href="{{ route('support.thread', $ticket->token) }}" style="display:inline-block; background-color:#17130a; color:#ffc61a; font-weight:bold; font-size:14px; text-decoration:none; padding:14px 22px;">Buka percakapan</a>
                    <p style="margin:18px 0 0; font-size:12px; color:#5c5340;">Kode tiket: <span style="font-family:'Courier New', monospace; font-weight:bold;">{{ $ticket->code }}</span>. Tautan di atas bersifat pribadi, jangan dibagikan.</p>
                </td></tr>
            </table>
        </td></tr>
    </table>
</body>
</html>
