<div class="max-w-2xl">
    <div class="page-head">
        <div>
            <h1 class="page-title">Verifikasi Integritas</h1>
            <p class="page-sub">Menghitung ulang seluruh rantai hash activity log dan membandingkannya dengan yang tersimpan.</p>
        </div>
    </div>

    <button type="button" wire:click="verify" class="btn btn-paint">Verifikasi Sekarang</button>

    @if ($result)
        @if ($result['valid'])
            <div class="mt-6 bg-paid-wash border-2 border-paid text-paid p-5">
                <p class="font-bold text-lg">Rantai log utuh, tidak ada indikasi manipulasi.</p>
            </div>
        @else
            <div class="mt-6 bg-paint-wash border-2 border-paint text-paint-dark p-5">
                <p class="font-bold text-lg">Ketidaksesuaian terdeteksi pada baris #{{ $result['tampered_at'] }}.</p>
            </div>
        @endif
    @endif
</div>
