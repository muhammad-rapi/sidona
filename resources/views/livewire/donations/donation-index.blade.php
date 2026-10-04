<div>
    <div class="page-head">
        <div>
            <h1 class="page-title">Riwayat Donasi</h1>
            <p class="page-sub">Donasi dikonfirmasi otomatis saat pembayaran diterima. Halaman ini hanya untuk memantau; tidak ada yang perlu diverifikasi manual.</p>
        </div>
    </div>

    <p class="mb-6 max-w-2xl text-lg">
        <strong class="text-paid">Rp&nbsp;{{ number_format($paidTotal, 0, ',', '.') }}</strong> sudah masuk dari semua donasi berhasil.
        @if ($pendingCount > 0)
            <span class="text-ink-soft">{{ number_format($pendingCount, 0, ',', '.') }} lagi masih menunggu pembayaran.</span>
        @endif
    </p>

    <div class="mb-4 flex flex-wrap items-end justify-between gap-4">
        <div class="flex flex-wrap gap-2" role="group" aria-label="Filter status">
            @foreach (['all' => 'Semua', 'verified' => 'Berhasil', 'pending' => 'Menunggu', 'rejected' => 'Gagal'] as $value => $label)
                <button type="button" wire:click="$set('status', '{{ $value }}')" aria-pressed="{{ $status === $value ? 'true' : 'false' }}" class="btn btn-sm {{ $status === $value ? 'btn-ink' : 'btn-line' }}">{{ $label }}</button>
            @endforeach
        </div>
        <div class="w-full sm:w-72">
            <label for="search" class="sr-only">Cari donasi</label>
            <input id="search" type="search" wire:model.live.debounce.300ms="search" placeholder="Cari nama, kontak, atau kode" class="field field-sm">
        </div>
    </div>

    <div class="panel overflow-x-auto">
        <table class="ledger">
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Donatur</th>
                    <th>Program</th>
                    <th class="num">Nominal</th>
                    <th>Metode</th>
                    <th>Jam</th>
                    <th>Status</th>
                    <th class="sticky-col"><span class="sr-only">Aksi</span></th>
                </tr>
            </thead>
            <tbody>
                @php $lastDay = null; @endphp
                @forelse ($donations as $donation)
                    @php
                        $day = ($donation->paid_at ?? $donation->created_at)->timezone('Asia/Jakarta');
                        $dayKey = $day->toDateString();
                    @endphp
                    @if ($dayKey !== $lastDay)
                        @php $lastDay = $dayKey; @endphp
                        <tr>
                            <td colspan="8" class="!border-b-2 !border-ink !bg-transparent !px-4 !pb-2 !pt-6">
                                <div class="flex flex-wrap items-baseline gap-x-5 gap-y-0.5">
                                    <p class="text-base font-extrabold">{{ $day->locale('id')->translatedFormat('l, j F Y') }}</p>
                                    <p class="text-sm text-ink-soft">{{ $dayTotals[$dayKey]['count'] ?? 0 }} donasi berhasil &middot; <span class="font-bold text-ink">Rp&nbsp;{{ number_format($dayTotals[$dayKey]['sum'] ?? 0, 0, ',', '.') }}</span></p>
                                </div>
                            </td>
                        </tr>
                    @endif
                    @php
                        $badge = match ($donation->status) {
                            \App\Enums\DonationStatus::Verified => 'badge-paid',
                            \App\Enums\DonationStatus::Rejected => 'badge-fail',
                            default => 'badge-wait',
                        };
                    @endphp
                    <tr>
                        <td class="font-mono text-xs">{{ $donation->reference_code }}</td>
                        <td>
                            <p class="font-semibold">{{ $donation->donor_name }}@if ($donation->is_anonymous) <span class="badge badge-ink ml-1">Anonim</span>@endif</p>
                            <p class="text-xs text-ink-soft">{{ $donation->donor_contact }}</p>
                        </td>
                        <td>{{ $donation->campaign->name }}</td>
                        <td class="num font-bold">Rp&nbsp;{{ number_format($donation->amount, 0, ',', '.') }}</td>
                        <td>{{ $donation->payment_method?->label() ?? '-' }}</td>
                        <td class="whitespace-nowrap text-ink-soft">{{ ($donation->paid_at ?? $donation->created_at)->timezone('Asia/Jakarta')->format('H:i') }}</td>
                        <td><span class="badge {{ $badge }}">{{ $donation->status->label() }}</span></td>
                        <td class="sticky-col text-right">
                            <x-action :icon="$detailId === $donation->id ? 'close' : 'detail'" :label="$detailId === $donation->id ? 'Tutup detail' : 'Lihat detail'" wire:click="toggleDetail({{ $donation->id }})" aria-expanded="{{ $detailId === $donation->id ? 'true' : 'false' }}" />
                        </td>
                    </tr>
                    @if ($detailId === $donation->id)
                        @include('livewire.partials.donation-detail-row', ['donation' => $donation, 'trail' => $trail, 'colspan' => 8])
                    @endif
                @empty
                    <tr>
                        <td colspan="8" class="py-12 text-center text-ink-soft">Belum ada donasi yang cocok dengan filter ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">{{ $donations->links() }}</div>
</div>
