<div>
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-xl font-semibold">Laporan Penyaluran</h1>
        <button type="button" wire:click="exportPdf" class="rounded bg-slate-900 px-4 py-2 text-white text-sm">Unduh PDF</button>
    </div>

    <form class="flex flex-wrap gap-3 mb-4 bg-white p-4 rounded border border-slate-300">
        <select wire:model.live="campaign_id" class="rounded border border-slate-300 px-3 py-2 text-sm">
            <option value="">Semua Program</option>
            @foreach ($campaigns as $campaign)
                <option value="{{ $campaign->id }}">{{ $campaign->name }}</option>
            @endforeach
        </select>
        <select wire:model.live="status" class="rounded border border-slate-300 px-3 py-2 text-sm">
            <option value="">Semua Status</option>
            <option value="submitted">Diajukan</option>
            <option value="approved">Disetujui</option>
            <option value="rejected">Ditolak</option>
        </select>
        <input type="date" wire:model.live="from" class="rounded border border-slate-300 px-3 py-2 text-sm">
        <input type="date" wire:model.live="to" class="rounded border border-slate-300 px-3 py-2 text-sm">
    </form>

    <table class="w-full border border-slate-300 text-sm bg-white">
        <thead class="bg-slate-200">
            <tr>
                <th class="border border-slate-300 px-3 py-2 text-left">Program</th>
                <th class="border border-slate-300 px-3 py-2 text-left">Jumlah</th>
                <th class="border border-slate-300 px-3 py-2 text-left">Keterangan</th>
                <th class="border border-slate-300 px-3 py-2 text-left">Diajukan Oleh</th>
                <th class="border border-slate-300 px-3 py-2 text-left">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($disbursements as $disbursement)
                <tr>
                    <td class="border border-slate-300 px-3 py-2">{{ $disbursement->campaign->name }}</td>
                    <td class="border border-slate-300 px-3 py-2">Rp {{ number_format($disbursement->amount, 0, ',', '.') }}</td>
                    <td class="border border-slate-300 px-3 py-2">{{ $disbursement->description }}</td>
                    <td class="border border-slate-300 px-3 py-2">{{ $disbursement->submitter->name }}</td>
                    <td class="border border-slate-300 px-3 py-2">{{ $disbursement->status->label() }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="border border-slate-300 px-3 py-6 text-center text-slate-500">Tidak ada penyaluran.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="mt-4">{{ $disbursements->links() }}</div>
</div>
