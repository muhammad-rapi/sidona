<div class="max-w-xl">
    <h1 class="text-xl font-semibold mb-2">Verifikasi Integritas</h1>
    <p class="text-sm text-graphite mb-6">Menghitung ulang seluruh rantai hash activity log dan membandingkannya dengan yang tersimpan.</p>

    <button type="button" wire:click="verify" class="rounded-[40px] bg-coral-pulse px-4 py-2 text-white text-sm">Verifikasi Sekarang</button>

    @if ($result)
        @if ($result['valid'])
            <div class="mt-6 bg-white p-6 rounded border border-leaf-bright">
                <p class="text-sm text-canopy-green">Rantai log utuh, tidak ada indikasi manipulasi.</p>
            </div>
        @else
            <div class="mt-6 bg-white p-6 rounded border border-red-700">
                <p class="text-sm text-red-800">Ketidaksesuaian terdeteksi pada baris #{{ $result['tampered_at'] }}.</p>
            </div>
        @endif
    @endif
</div>
