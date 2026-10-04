@props(['name'])

@php
    $paths = [
        'detail' => 'M3 12s3.5-7 9-7 9 7 9 7-3.5 7-9 7-9-7-9-7Zm9 3a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z',
        'close' => 'M6 6l12 12M18 6 6 18',
        'view' => 'M14 4h6v6M20 4l-9 9M18 14v5a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h5',
        'edit' => 'M4 20h4L19 9l-4-4L4 16v4ZM13.5 6.5l4 4',
        'trash' => 'M4 7h16M9 7V4h6v3M6 7l1 13h10l1-13M10 11v6M14 11v6',
        'approve' => 'M5 12.5 10 17.5 19 7',
        'reject' => 'M6 6l12 12M18 6 6 18',
        'send' => 'M4 12h14M12 5l7 7-7 7',
        'plus' => 'M12 5v14M5 12h14',
        'download' => 'M12 4v11M7 11l5 5 5-5M5 20h14',
        'check' => 'M5 12.5 10 17.5 19 7',
    ];
@endphp

<svg {{ $attributes->class('h-[1.15rem] w-[1.15rem] shrink-0') }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="square" stroke-linejoin="miter" aria-hidden="true"><path d="{{ $paths[$name] ?? $paths['detail'] }}"/></svg>
