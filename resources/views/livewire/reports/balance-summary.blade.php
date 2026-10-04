@php
    $totalRaised = (int) $campaigns->sum('raised_sum');
    $totalOut = (int) $campaigns->sum('disbursed_sum');
@endphp

<div>
    <div class="page-head">
        <div>
            <h1 class="page-title">Ringkasan saldo</h1>
            <p class="page-sub">Posisi dana tiap program: yang masuk, yang sudah disalurkan, dan yang masih tersedia. Bar menunjukkan porsi dana yang sudah disalurkan (gelap).</p>
        </div>
        <div>
            <button type="button" wire:click="exportPdf" class="btn btn-paint"><x-icon name="download" />Unduh PDF</button>
        </div>
    </div>

    <div class="panel overflow-x-auto">
        <table class="ledger">
            <thead>
                <tr>
                    <th>Program</th>
                    <th class="num">Donasi berhasil</th>
                    <th class="num">Disalurkan</th>
                    <th class="num">Saldo tersedia</th>
                    <th class="min-w-[9rem]">Sudah disalurkan</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($campaigns as $campaign)
                    @php
                        $raised = (int) $campaign->raised_sum;
                        $out = (int) $campaign->disbursed_sum;
                        $share = $raised > 0 ? min(100, (int) round($out / $raised * 100)) : 0;
                    @endphp
                    <tr>
                        <td>
                            <p class="font-bold">{{ $campaign->name }}</p>
                            @if ($campaign->bank_name && $campaign->account_number)
                                <p class="text-xs text-ink-soft">{{ $campaign->bank_name }} <span class="font-mono">{{ $campaign->account_number }}</span> a.n. {{ $campaign->account_holder }}</p>
                            @endif
                        </td>
                        <td class="num">Rp&nbsp;{{ number_format($raised, 0, ',', '.') }}</td>
                        <td class="num">Rp&nbsp;{{ number_format($out, 0, ',', '.') }}</td>
                        <td class="num font-bold">Rp&nbsp;{{ number_format($raised - $out, 0, ',', '.') }}</td>
                        <td>
                            <div class="thermo-h !h-2.5" style="--level: {{ $share }}" role="img" aria-label="{{ $share }} persen sudah disalurkan"><i class="!bg-ink"></i></div>
                            <p class="mt-1 text-xs text-ink-soft">{{ $share }}%</p>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="py-6 text-center text-ink-soft">Belum ada program donasi.</td>
                    </tr>
                @endforelse
            </tbody>
            @if ($campaigns->isNotEmpty())
                <tfoot>
                    <tr class="border-t-2 border-ink">
                        <td class="px-4 py-4 font-extrabold">Jumlah semua program</td>
                        <td class="px-4 py-4 text-right font-extrabold">Rp&nbsp;{{ number_format($totalRaised, 0, ',', '.') }}</td>
                        <td class="px-4 py-4 text-right font-extrabold">Rp&nbsp;{{ number_format($totalOut, 0, ',', '.') }}</td>
                        <td class="px-4 py-4 text-right font-extrabold text-paid">Rp&nbsp;{{ number_format($totalRaised - $totalOut, 0, ',', '.') }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>
</div>
