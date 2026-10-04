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
        'home' => 'M3 12 12 4l9 8M5 10v10h14V10',
        'programs' => 'M4 5h16v4H4zM4 13h16v6H4z',
        'coins' => 'M12 3v18M17 7H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6',
        'users' => 'M16 20v-1a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v1M9.5 11a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7ZM21 20v-1a4 4 0 0 0-3-3.87M16 4.13a3.5 3.5 0 0 1 0 6.74',
        'shield' => 'M12 3l8 3v6c0 5-3.5 8-8 9-4.5-1-8-4-8-9V6l8-3ZM8.5 12l2.5 2.5L16 9.5',
        'list' => 'M8 6h12M8 12h12M8 18h12M4 6h.01M4 12h.01M4 18h.01',
        'login' => 'M10 17l5-5-5-5M15 12H3M14 4h5a1 1 0 0 1 1 1v14a1 1 0 0 1-1 1h-5',
        'alert' => 'M12 4 2.5 20h19L12 4ZM12 10v5M12 18h.01',
        'report' => 'M6 3h9l4 4v14H6V3ZM14 3v5h5M9 13h7M9 17h7',
        'wallet' => 'M4 7h15a1 1 0 0 1 1 1v10a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V7Zm0 0V6a1 1 0 0 1 1-1h11M16 13h2',
        'stamp' => 'M12 3a4 4 0 0 1 4 4c0 2-1 3-1 5h-6c0-2-1-3-1-5a4 4 0 0 1 4-4ZM5 15h14v4H5v-4Z',
    ];
@endphp

<svg {{ $attributes->class('h-[1.15rem] w-[1.15rem] shrink-0') }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="square" stroke-linejoin="miter" aria-hidden="true"><path d="{{ $paths[$name] ?? $paths['detail'] }}"/></svg>
