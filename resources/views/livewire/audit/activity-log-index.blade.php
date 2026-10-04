<div>
    <div class="page-head">
        <div>
            <h1 class="page-title">Log aktivitas</h1>
            <p class="page-sub">Setiap perubahan penting tercatat di sini. Kolom rantai menunjukkan sidik hash tiap catatan dan hash catatan sebelumnya, sehingga perubahan diam-diam akan terlihat.</p>
        </div>
        <div>
            <button type="button" wire:click="exportPdf" class="btn btn-paint"><x-icon name="download" />Unduh PDF</button>
        </div>
    </div>

    <form class="mb-5 flex flex-wrap items-end gap-3" aria-label="Filter log">
        <div>
            <label for="f-action" class="sr-only">Jenis aksi</label>
            <select id="f-action" wire:model.live="action" class="field field-sm w-auto max-w-[16rem]">
                <option value="">Semua jenis aksi</option>
                @foreach ($actionOptions as $code => $label)
                    <option value="{{ $code }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="f-user" class="sr-only">Pelaku</label>
            <select id="f-user" wire:model.live="user_id" class="field field-sm w-auto max-w-[14rem]">
                <option value="">Semua pelaku</option>
                @foreach ($userOptions as $option)
                    <option value="{{ $option->id }}">{{ $option->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex items-center gap-2">
            <label for="f-from" class="sr-only">Dari tanggal</label>
            <input id="f-from" type="date" wire:model.live="from" class="field field-sm w-auto">
            <span class="text-ink-soft" aria-hidden="true">&ndash;</span>
            <label for="f-to" class="sr-only">Sampai tanggal</label>
            <input id="f-to" type="date" wire:model.live="to" class="field field-sm w-auto">
        </div>
    </form>

    <div class="panel overflow-x-auto">
        <table class="ledger">
            <thead>
                <tr>
                    <th>Jam</th>
                    <th>Kejadian</th>
                    <th>Pelaku</th>
                    <th>Rantai</th>
                    <th class="sticky-col"><span class="sr-only">Aksi</span></th>
                </tr>
            </thead>
            <tbody>
                @php $lastDay = null; @endphp
                @forelse ($entries as $entry)
                    @php
                        $at = $entry->created_at->timezone('Asia/Jakarta');
                        $dayKey = $at->toDateString();
                        $actor = $entry->user?->name ?? ($entry->action === 'donation.paid' ? 'Sistem (otomatis)' : 'Tamu');
                    @endphp
                    @if ($dayKey !== $lastDay)
                        @php $lastDay = $dayKey; @endphp
                        <tr>
                            <td colspan="5" class="!border-b-2 !border-ink !bg-transparent !px-4 !pb-2 !pt-6">
                                <p class="text-base font-extrabold">{{ $at->locale('id')->translatedFormat('l, j F Y') }}</p>
                            </td>
                        </tr>
                    @endif
                    <tr>
                        <td class="whitespace-nowrap font-mono text-xs text-ink-soft">{{ $at->format('H:i:s') }}</td>
                        <td>
                            <p class="font-bold">{{ \App\Support\ActivityLabels::label($entry->action) }}</p>
                            <p class="text-sm text-ink-soft">{{ $entry->subjectLabel() }}</p>
                        </td>
                        <td class="whitespace-nowrap">{{ $actor }}</td>
                        <td class="whitespace-nowrap font-mono text-xs text-ink-soft" title="hash sebelumnya &rarr; hash catatan ini">
                            <span>{{ substr($entry->prev_hash, 0, 6) }}</span> &rarr; <span class="font-bold text-ink">{{ substr($entry->hash, 0, 10) }}</span>
                            <span class="block text-ink-faint">#{{ $entry->id }}</span>
                        </td>
                        <td class="sticky-col">
                            <x-action :icon="$expandedId === $entry->id ? 'close' : 'detail'" :label="$expandedId === $entry->id ? 'Tutup perubahan' : 'Lihat perubahan data'" wire:click="toggle({{ $entry->id }})" />
                        </td>
                    </tr>
                    @if ($expandedId === $entry->id)
                        <tr>
                            <td colspan="5" class="!bg-desk !px-5 !py-4">
                                @php
                                    $keys = array_unique(array_merge(array_keys($entry->before ?? []), array_keys($entry->after ?? [])));
                                    $changes = collect($keys)->filter(fn ($k) => data_get($entry->before, $k) !== data_get($entry->after, $k));
                                @endphp
                                <p class="mb-2 text-xs font-bold uppercase tracking-wider text-ink-soft">Kode: <span class="font-mono normal-case">{{ $entry->action }}</span></p>
                                @if ($changes->isEmpty())
                                    <p class="text-sm text-ink-soft">Tidak ada perubahan nilai yang tercatat untuk kejadian ini.</p>
                                @else
                                    <table class="w-full text-sm">
                                        <thead><tr class="text-left text-xs text-ink-soft"><th class="w-40 py-1 pr-4 font-semibold">Isian</th><th class="py-1 pr-4 font-semibold">Sebelum</th><th class="py-1 font-semibold">Sesudah</th></tr></thead>
                                        <tbody>
                                            @foreach ($changes as $key)
                                                @php
                                                    $b = data_get($entry->before, $key);
                                                    $a = data_get($entry->after, $key);
                                                @endphp
                                                <tr class="align-top">
                                                    <td class="py-1 pr-4 font-semibold">{{ $key }}</td>
                                                    <td class="py-1 pr-4 text-ink-soft [overflow-wrap:anywhere]">{{ $b === null ? '-' : (is_scalar($b) ? $b : json_encode($b)) }}</td>
                                                    <td class="py-1 [overflow-wrap:anywhere]">{{ $a === null ? '-' : (is_scalar($a) ? $a : json_encode($a)) }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                @endif
                            </td>
                        </tr>
                    @endif
                @empty
                    <tr>
                        <td colspan="5" class="py-10 text-center text-ink-soft">Tidak ada catatan aktivitas.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $entries->links() }}</div>
</div>
