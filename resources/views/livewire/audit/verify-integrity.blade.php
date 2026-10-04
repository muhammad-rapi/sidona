<div>
    <div class="page-head">
        <div>
            <h1 class="page-title">Verifikasi integritas</h1>
            <p class="page-sub">Menghitung ulang seluruh rantai hash log aktivitas dari catatan pertama dan membandingkannya dengan yang tersimpan. Satu catatan yang diubah akan membuat semua catatan sesudahnya tidak cocok.</p>
        </div>
    </div>

    <div class="grid gap-x-12 gap-y-8 lg:grid-cols-[1fr_1.2fr]">
        <div>
            <p class="text-lg">
                <strong class="font-extrabold">{{ number_format($total, 0, ',', '.') }}</strong> catatan
                @if ($first)
                    sejak {{ $first->created_at->timezone('Asia/Jakarta')->locale('id')->translatedFormat('j F Y') }}.
                @endif
            </p>

            <button type="button" wire:click="verify" class="btn btn-paint btn-lg mt-5" wire:loading.attr="disabled" wire:target="verify">
                <x-icon name="shield" />
                <span wire:loading.remove wire:target="verify">Verifikasi sekarang</span>
                <span wire:loading wire:target="verify">Menghitung ulang&hellip;</span>
            </button>

            @if ($result)
                @if ($result['valid'])
                    <div class="mt-6 border-2 border-paid bg-paid-wash p-5" role="status">
                        <p class="text-xl font-extrabold tracking-tight text-paid">Rantai utuh.</p>
                        <p class="mt-1 text-ink">Semua {{ number_format($result['checked'], 0, ',', '.') }} catatan cocok. Tidak ada indikasi manipulasi.</p>
                        <p class="mt-2 text-xs text-ink-soft">Diperiksa {{ $result['at'] }} WIB oleh {{ auth()->user()->name }}.</p>
                    </div>
                @else
                    <div class="mt-6 border-2 border-paint bg-paint-wash p-5" role="alert">
                        <p class="text-xl font-extrabold tracking-tight text-paint-dark">Ketidaksesuaian terdeteksi.</p>
                        <p class="mt-1 text-ink">Rantai pertama kali putus pada catatan <strong>#{{ $result['tampered_at'] }}</strong>. Catatan sebelumnya masih cocok.</p>
                        @if ($result['culprit'])
                            <p class="mt-2 text-sm text-ink">{{ $result['culprit']['label'] }}, {{ $result['culprit']['at'] }} WIB oleh {{ $result['culprit']['by'] }}.</p>
                        @endif
                        <a href="{{ route('audit.activity') }}" wire:navigate class="mt-3 inline-block text-sm font-bold underline underline-offset-4">Buka log aktivitas</a>
                    </div>
                @endif
            @endif
        </div>

        <div>
            <p class="mb-2 text-sm font-bold text-ink-soft">Lima catatan terakhir (hash sebelumnya &rarr; hash catatan)</p>
            <ol class="border-t-2 border-ink font-mono text-xs">
                @foreach ($recent as $row)
                    <li class="flex items-baseline justify-between gap-4 border-b border-ink/15 py-2.5">
                        <span><span class="text-ink-faint">#{{ $row->id }}</span> <span class="font-sans text-sm font-semibold">{{ \App\Support\ActivityLabels::label($row->action) }}</span></span>
                        <span class="text-ink-soft">{{ substr($row->prev_hash, 0, 6) }} &rarr; <span class="font-bold text-ink">{{ substr($row->hash, 0, 10) }}</span></span>
                    </li>
                @endforeach
            </ol>
        </div>
    </div>
</div>
