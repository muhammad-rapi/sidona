@php
    $status = $campaign->status;
    $tone = match ($status->value) {
        'active', 'completed' => ['border-paid bg-paid-wash', 'text-paid'],
        'rejected' => ['border-paint bg-paint-wash', 'text-paint-dark'],
        default => ['border-ink bg-board-wash', 'text-ink'],
    };
@endphp

<div class="mx-auto max-w-3xl px-5 py-14">
    <h1 class="paint-type text-5xl text-ink sm:text-6xl">Status pengajuan</h1>
    <p class="mt-3 text-ink-soft">Kode <span class="font-mono font-bold text-ink">{{ $campaign->proposal_code }}</span> &middot; {{ $campaign->name }}</p>

    <div class="mt-8 border-2 p-6 {{ $tone[0] }}" role="status">
        <p class="paint-type text-4xl {{ $tone[1] }}">{{ $status->label() }}</p>
        <p class="mt-3 max-w-xl text-ink">
            @switch($status->value)
                @case('pending')
                    Pengajuan Anda sudah masuk dan sedang dibaca admin. Biasanya tidak lama. Buka lagi halaman ini nanti, atau tunggu kabar lewat kontak yang Anda isi.
                    @break
                @case('rejected')
                    Pengajuan belum bisa disetujui. Alasan: <strong>{{ $campaign->rejection_reason }}</strong>. Anda boleh mengajukan lagi setelah data diperbaiki.
                    @break
                @default
                    Program Anda sudah tayang dan bisa menerima donasi.
            @endswitch
        </p>

        @if ($status->value === 'pending' && $campaign->proposerNeedsVerification())
            <p class="mt-4 border-t-2 border-ink pt-3 text-sm font-semibold text-paint-dark">Email Anda belum dikonfirmasi. Buka email dari SIDONA dan tekan tombol konfirmasi, supaya admin bisa menyetujui.</p>
        @endif

        @if ($status->value === 'active')
            <a href="{{ route('program.show', $campaign) }}" wire:navigate class="btn btn-ink mt-5">Buka halaman program</a>
        @elseif ($status->value === 'rejected')
            <a href="{{ route('program.submit') }}" wire:navigate class="btn btn-ink mt-5">Ajukan lagi</a>
        @endif
    </div>

    <p class="mt-6 text-sm text-ink-soft">Diajukan {{ $campaign->created_at->timezone('Asia/Jakarta')->translatedFormat('j F Y, H:i') }} WIB oleh {{ $campaign->proposer_name }}.</p>
</div>
