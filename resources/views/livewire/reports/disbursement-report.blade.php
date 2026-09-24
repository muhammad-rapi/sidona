<div>
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-xl font-semibold">Laporan Penyaluran</h1>
        <button type="button" wire:click="exportPdf" class="rounded-full bg-coral-pulse px-6 py-3 text-white text-sm font-medium transition-colors duration-200 hover:bg-coral-pulse-dark">Unduh PDF</button>
    </div>

    <form class="flex flex-wrap gap-3 mb-4 bg-white p-4 rounded-2xl border border-frost-gray">
        <select wire:model.live="campaign_id" class="rounded border border-frost-gray px-3 py-2 text-sm">
            <option value="">Semua Program</option>
            @foreach ($campaigns as $campaign)
                <option value="{{ $campaign->id }}">{{ $campaign->name }}</option>
            @endforeach
        </select>
        <select wire:model.live="status" class="rounded border border-frost-gray px-3 py-2 text-sm">
            <option value="">Semua Status</option>
            <option value="submitted">Diajukan</option>
            <option value="approved">Disetujui</option>
            <option value="rejected">Ditolak</option>
        </select>
        <input type="date" wire:model.live="from" class="rounded border border-frost-gray px-3 py-2 text-sm">
        <input type="date" wire:model.live="to" class="rounded border border-frost-gray px-3 py-2 text-sm">
    </form>

    <table class="w-full text-sm">
        <thead>
            <tr>
                <th class="border-b-2 border-ink-black px-4 py-3 text-left font-medium text-graphite">Program</th>
                <th class="border-b-2 border-ink-black px-4 py-3 text-left font-medium text-graphite">Jumlah</th>
                <th class="border-b-2 border-ink-black px-4 py-3 text-left font-medium text-graphite">Keterangan</th>
                <th class="border-b-2 border-ink-black px-4 py-3 text-left font-medium text-graphite">Diajukan Oleh</th>
                <th class="border-b-2 border-ink-black px-4 py-3 text-left font-medium text-graphite">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($disbursements as $disbursement)
                <tr>
                    <td class="border-b border-frost-gray px-4 py-3">{{ $disbursement->campaign->name }}</td>
                    <td class="border-b border-frost-gray px-4 py-3 font-mono">Rp {{ number_format($disbursement->amount, 0, ',', '.') }}</td>
                    <td class="border-b border-frost-gray px-4 py-3">{{ $disbursement->description }}</td>
                    <td class="border-b border-frost-gray px-4 py-3">{{ $disbursement->submitter->name }}</td>
                    <td class="border-b border-frost-gray px-4 py-3">{{ $disbursement->status->label() }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="border-b border-frost-gray px-4 py-6 text-center text-graphite">Tidak ada penyaluran.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="mt-4">{{ $disbursements->links() }}</div>
</div>
