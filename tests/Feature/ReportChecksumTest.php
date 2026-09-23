<?php

use App\Services\ReportChecksum;

it('produces a checksum that verifies against the final stamped pdf bytes', function () {
    $checksumService = app(ReportChecksum::class);
    $draft = $checksumService->renderDraft('<html><body>Contoh isi laporan. '.$checksumService->footerHtml().'</body></html>');
    [$checksum, $final] = $checksumService->stamp($draft);

    $result = $checksumService->verify($final);

    expect($result['valid'])->toBeTrue();
    expect($result['checksum'])->toBe($checksum);
});

it('detects a modified pdf as invalid', function () {
    $checksumService = app(ReportChecksum::class);
    $draft = $checksumService->renderDraft('<html><body>Contoh isi laporan. '.$checksumService->footerHtml().'</body></html>');
    [, $final] = $checksumService->stamp($draft);

    $tampered = str_replace('Contoh', 'Diubah', $final);

    expect($checksumService->verify($tampered)['valid'])->toBeFalse();
});

it('reports invalid without erroring when there is no checksum footer at all', function () {
    $result = app(ReportChecksum::class)->verify('bukan berkas pdf sama sekali');

    expect($result['valid'])->toBeFalse();
    expect($result['checksum'])->toBeNull();
});
