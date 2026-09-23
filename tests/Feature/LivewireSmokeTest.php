<?php

use App\Livewire\SmokeTest;
use Livewire\Livewire;

it('renders the livewire smoke test component', function () {
    Livewire::test(SmokeTest::class)
        ->assertSee('SIDONA siap jalan');
});
