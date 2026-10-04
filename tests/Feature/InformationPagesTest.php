<?php

it('serves the FAQ, terms and privacy pages to guests', function (string $route, string $text) {
    $this->get(route($route))->assertOk()->assertSee($text);
})->with([
    'faq' => ['faq', 'Pertanyaan umum'],
    'terms' => ['terms', 'Syarat dan ketentuan'],
    'privacy' => ['privacy', 'Kebijakan privasi'],
]);

it('links the information pages from the public footer without exposing the staff login', function () {
    $this->get(route('program.index'))
        ->assertSee(route('faq'), false)
        ->assertSee(route('terms'), false)
        ->assertSee(route('privacy'), false)
        ->assertDontSee(route('login'), false);
});

it('states the real behaviour of the product in the FAQ', function () {
    $this->get(route('faq'))
        ->assertSee('Tidak ada admin yang perlu menyetujui donasi')
        ->assertSee('mode simulasi');
});
