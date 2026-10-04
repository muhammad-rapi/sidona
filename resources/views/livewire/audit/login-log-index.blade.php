<div>
    <div class="page-head">
        <div>
            <h1 class="page-title">Log login</h1>
            <p class="page-sub">
                Hari ini {{ $today['total'] }} percobaan masuk, {{ $today['failed'] }} gagal.
                @if (count($flaggedEmails) > 0)
                    <a href="{{ route('audit.anomalies') }}" wire:navigate class="font-bold text-paint-dark underline underline-offset-4">{{ count($flaggedEmails) }} akun menunjukkan pola gagal berturut-turut.</a>
                @else
                    Tidak ada pola gagal berturut-turut.
                @endif
            </p>
        </div>
        <div>
            <button type="button" wire:click="exportPdf" class="btn btn-paint"><x-icon name="download" />Unduh PDF</button>
        </div>
    </div>

    <div class="mb-5 flex flex-wrap items-end justify-between gap-4">
        <div class="flex flex-wrap gap-2" role="group" aria-label="Filter status">
            @foreach (['' => 'Semua', 'success' => 'Berhasil', 'failed' => 'Gagal'] as $value => $label)
                <button type="button" wire:click="$set('status', '{{ $value }}')" aria-pressed="{{ $status === (string) $value ? 'true' : 'false' }}" class="btn btn-sm {{ $status === (string) $value ? 'btn-ink' : 'btn-line' }}">{{ $label }}</button>
            @endforeach
        </div>
        <form class="flex flex-wrap items-center gap-3" aria-label="Filter log login">
            <label for="l-email" class="sr-only">Email</label>
            <input id="l-email" type="search" wire:model.live.debounce.300ms="email" placeholder="Cari email" class="field field-sm w-56">
            <div class="flex items-center gap-2">
                <label for="l-from" class="sr-only">Dari tanggal</label>
                <input id="l-from" type="date" wire:model.live="from" class="field field-sm w-auto">
                <span class="text-ink-soft" aria-hidden="true">&ndash;</span>
                <label for="l-to" class="sr-only">Sampai tanggal</label>
                <input id="l-to" type="date" wire:model.live="to" class="field field-sm w-auto">
            </div>
        </form>
    </div>

    <div class="panel overflow-x-auto">
        <table class="ledger">
            <thead>
                <tr>
                    <th>Jam</th>
                    <th>Akun</th>
                    <th>Perangkat</th>
                    <th>Alamat IP</th>
                    <th>Hasil</th>
                </tr>
            </thead>
            <tbody>
                @php $lastDay = null; @endphp
                @forelse ($entries as $entry)
                    @php
                        $at = $entry->created_at->timezone('Asia/Jakarta');
                        $dayKey = $at->toDateString();
                        $failed = $entry->status !== 'success';
                        $flagged = $failed && in_array($entry->email, $flaggedEmails, true);
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
                            <p class="font-semibold">{{ $entry->email }}</p>
                            @if ($entry->user)
                                <p class="text-xs text-ink-soft">{{ $entry->user->name }} &middot; {{ $entry->user->role->label() }}</p>
                            @elseif ($failed)
                                <p class="text-xs text-ink-soft">tidak dikenali atau kata sandi salah</p>
                            @endif
                        </td>
                        <td title="{{ $entry->user_agent }}">{{ $entry->deviceLabel() }}</td>
                        <td class="whitespace-nowrap font-mono text-xs">{{ $entry->ip_address }}</td>
                        <td>
                            <span class="badge {{ $failed ? 'badge-fail' : 'badge-paid' }}">{{ $failed ? 'Gagal' : 'Berhasil' }}</span>
                            @if ($flagged)
                                <p class="mt-1 text-xs font-bold text-paint-dark">Pola gagal berturut-turut</p>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="py-10 text-center text-ink-soft">Tidak ada catatan login.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $entries->links() }}</div>
</div>
