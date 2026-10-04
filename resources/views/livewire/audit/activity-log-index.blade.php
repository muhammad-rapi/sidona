<div>
    <div class="page-head">
        <div>
            <h1 class="page-title">Log Aktivitas</h1>
        </div>
        <div>
            <button type="button" wire:click="exportPdf" class="btn btn-paint">Unduh PDF</button>
        </div>
    </div>

    <form class="panel p-4 mb-4 flex flex-wrap gap-3 items-end">
        <input type="text" wire:model.live="action" placeholder="Jenis aksi" class="field field-sm w-auto">
        <input type="date" wire:model.live="from" class="field field-sm w-auto">
        <input type="date" wire:model.live="to" class="field field-sm w-auto">
    </form>

    <div class="panel overflow-x-auto">
        <table class="ledger">
            <thead>
                <tr>
                    <th>Waktu</th>
                    <th>Pengguna</th>
                    <th>Aksi</th>
                    <th>Objek</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($entries as $entry)
                    <tr>
                        <td class="font-mono text-xs">{{ $entry->created_at->format('d/m/Y H:i') }}</td>
                        <td>{{ $entry->user?->name ?? ($entry->action === 'donation.paid' ? 'Sistem (otomatis)' : 'Tamu') }}</td>
                        <td class="font-mono text-xs">{{ $entry->action }}</td>
                        <td>{{ $entry->subject_type ? class_basename($entry->subject_type).' #'.$entry->subject_id : '-' }}</td>
                        <td>
                            <button type="button" wire:click="toggle({{ $entry->id }})" class="btn btn-line btn-sm">Detail</button>
                        </td>
                    </tr>
                    @if ($expandedId === $entry->id)
                        <tr>
                            <td colspan="5" class="bg-desk">
                                <dl class="text-sm space-y-1">
                                    @foreach (array_unique(array_merge(array_keys($entry->before ?? []), array_keys($entry->after ?? []))) as $key)
                                        @php($beforeValue = data_get($entry->before, $key))
                                        @php($afterValue = data_get($entry->after, $key))
                                        @if ($beforeValue !== $afterValue)
                                            <div class="flex gap-2">
                                                <dt class="font-semibold w-32 shrink-0">{{ $key }}</dt>
                                                <dd class="break-all">{{ is_scalar($beforeValue) ? $beforeValue : json_encode($beforeValue) }} &rarr; {{ is_scalar($afterValue) ? $afterValue : json_encode($afterValue) }}</dd>
                                            </div>
                                        @endif
                                    @endforeach
                                </dl>
                            </td>
                        </tr>
                    @endif
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-ink-soft py-6">Tidak ada catatan aktivitas.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $entries->links() }}</div>
</div>
