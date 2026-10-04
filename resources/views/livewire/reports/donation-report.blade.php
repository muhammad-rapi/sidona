<div>
    <div class="page-head">
        <div>
            <h1 class="page-title">Laporan Donasi</h1>
        </div>
        <div>
            <button type="button" wire:click="exportPdf" class="btn btn-paint"><x-icon name="download" />Unduh PDF</button>
        </div>
    </div>

    <form class="panel p-4 mb-4 flex flex-wrap gap-3 items-end">
        <input type="search" wire:model.live.debounce.300ms="search" placeholder="Cari nama, kontak, atau kode" aria-label="Cari donasi" class="field field-sm w-full sm:w-64">
        <select wire:model.live="campaign_id" class="field field-sm w-auto">
            <option value="">Semua Program</option>
            @foreach ($campaigns as $campaign)
                <option value="{{ $campaign->id }}">{{ $campaign->name }}</option>
            @endforeach
        </select>
        <select wire:model.live="status" class="field field-sm w-auto">
            <option value="">Semua Status</option>
            <option value="pending">Menunggu pembayaran</option>
            <option value="verified">Berhasil</option>
            <option value="rejected">Gagal</option>
        </select>
        <input type="date" wire:model.live="from" class="field field-sm w-auto">
        <input type="date" wire:model.live="to" class="field field-sm w-auto">
    </form>

    <div class="panel overflow-x-auto">
        <table class="ledger">
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Program</th>
                    <th>Donatur</th>
                    <th class="num">Nominal</th>
                    <th>Status</th>
                    <th class="sticky-col"><span class="sr-only">Aksi</span></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($donations as $donation)
                    <tr>
                        <td class="font-mono text-xs break-all">{{ $donation->reference_code }}</td>
                        <td>{{ $donation->campaign->name }}</td>
                        <td>{{ $donation->donor_name }}</td>
                        <td class="num">Rp&nbsp;{{ number_format($donation->amount, 0, ',', '.') }}</td>
                        <td><span class="badge badge-ink">{{ $donation->status->label() }}</span></td>
                        <td class="sticky-col text-right">
                            <x-action :icon="$detailId === $donation->id ? 'close' : 'detail'" :label="$detailId === $donation->id ? 'Tutup detail' : 'Lihat detail'" wire:click="toggleDetail({{ $donation->id }})" aria-expanded="{{ $detailId === $donation->id ? 'true' : 'false' }}" />
                        </td>
                    </tr>
                    @if ($detailId === $donation->id)
                        @include('livewire.partials.donation-detail-row', ['donation' => $donation, 'trail' => $trail, 'colspan' => 6])
                    @endif
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-ink-soft py-6">Tidak ada donasi.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $donations->links() }}</div>
</div>
