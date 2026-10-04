<div>
    <div class="page-head">
        <div>
            <h1 class="page-title">Log Login</h1>
        </div>
        <div>
            <button type="button" wire:click="exportPdf" class="btn btn-paint"><x-icon name="download" />Unduh PDF</button>
        </div>
    </div>

    <form class="panel p-4 mb-4 flex flex-wrap gap-3 items-end">
        <input type="text" wire:model.live="email" placeholder="Email" class="field field-sm w-auto">
        <select wire:model.live="status" class="field field-sm w-auto">
            <option value="">Semua Status</option>
            <option value="success">Berhasil</option>
            <option value="failed">Gagal</option>
        </select>
        <input type="date" wire:model.live="from" class="field field-sm w-auto">
        <input type="date" wire:model.live="to" class="field field-sm w-auto">
    </form>

    <div class="panel overflow-x-auto">
        <table class="ledger">
            <thead>
                <tr>
                    <th>Waktu</th>
                    <th>Email</th>
                    <th>Alamat IP</th>
                    <th>Perangkat</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($entries as $entry)
                    <tr>
                        <td class="font-mono text-xs">{{ $entry->created_at->format('d/m/Y H:i') }}</td>
                        <td>{{ $entry->email }}</td>
                        <td class="font-mono text-xs">{{ $entry->ip_address }}</td>
                        <td title="{{ $entry->user_agent }}">{{ $entry->deviceLabel() }}</td>
                        <td>
                            <span class="badge {{ $entry->status === 'success' ? 'badge-paid' : 'badge-fail' }}">{{ $entry->status === 'success' ? 'Berhasil' : 'Gagal' }}</span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-ink-soft py-6">Tidak ada catatan login.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $entries->links() }}</div>
</div>
