<div>
    <div class="page-head">
        <div>
            <h1 class="page-title">Laporan Penyaluran</h1>
        </div>
        <div>
            <button type="button" wire:click="exportPdf" class="btn btn-paint">Unduh PDF</button>
        </div>
    </div>

    <form class="panel p-4 mb-4 flex flex-wrap gap-3 items-end">
        <select wire:model.live="campaign_id" class="field field-sm w-auto">
            <option value="">Semua Program</option>
            @foreach ($campaigns as $campaign)
                <option value="{{ $campaign->id }}">{{ $campaign->name }}</option>
            @endforeach
        </select>
        <select wire:model.live="status" class="field field-sm w-auto">
            <option value="">Semua Status</option>
            <option value="submitted">Diajukan</option>
            <option value="approved">Disetujui</option>
            <option value="rejected">Ditolak</option>
        </select>
        <input type="date" wire:model.live="from" class="field field-sm w-auto">
        <input type="date" wire:model.live="to" class="field field-sm w-auto">
    </form>

    <div class="panel overflow-x-auto">
        <table class="ledger">
            <thead>
                <tr>
                    <th>Program</th>
                    <th class="num">Jumlah</th>
                    <th>Keterangan</th>
                    <th>Diajukan Oleh</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($disbursements as $disbursement)
                    <tr>
                        <td>{{ $disbursement->campaign->name }}</td>
                        <td class="num">Rp&nbsp;{{ number_format($disbursement->amount, 0, ',', '.') }}</td>
                        <td>{{ $disbursement->description }}</td>
                        <td>{{ $disbursement->submitter->name }}</td>
                        <td><span class="badge badge-ink">{{ $disbursement->status->label() }}</span></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-ink-soft py-6">Tidak ada penyaluran.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $disbursements->links() }}</div>
</div>
