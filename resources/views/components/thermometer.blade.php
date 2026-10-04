@props([
    'percent' => 0,
    'from' => null,
    'rise' => false,
    'label' => null,
])

@php
    $level = max(0, min(100, (int) $percent));
    $start = $from === null ? $level : max(0, min(100, (int) $from));
@endphp

<div
    {{ $attributes->class(['thermo', 'thermo-rise' => $rise]) }}
    style="--level: {{ $level }}; --from: {{ $start }}"
    role="img"
    aria-label="{{ $label ?? "Terkumpul {$level} persen dari target" }}"
>
    <div class="relative">
        <div class="thermo-tube" style="height: calc(100% - 2.4rem)">
            <div class="thermo-mercury"></div>
        </div>
        <div class="thermo-bulb"></div>
    </div>
    <div class="thermo-scale" style="height: calc(100% - 2.4rem)" aria-hidden="true">
        @foreach ([0, 25, 50, 75, 100] as $mark)
            <span style="bottom: {{ $mark }}%">{{ $mark }}%</span>
        @endforeach
    </div>
</div>
