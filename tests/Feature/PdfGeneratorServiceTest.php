<?php

declare(strict_types=1);

use App\Services\PdfGeneratorService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('generates a PDF from a Blade view', function () {
    $svc = app(PdfGeneratorService::class);

    $ruta = $svc->generarPdfDesdeVista('pdf.conteo', [
        'conteo' => (object) ['numero' => 'TEST-001', 'detalles' => []],
        'empresa' => null,
    ], ['nombre' => 'test-pdf']);

    expect($ruta)->toBeFile()
        ->and(mime_content_type($ruta))->toBe('application/pdf')
        ->and(filesize($ruta))->toBeGreaterThan(1000);

    @unlink($ruta);
});

it('returns logo base64 when company has a logo_impresion', function () {
    $svc = app(PdfGeneratorService::class);

    $base64 = $svc->obtenerLogoBase64();

    if ($base64 === null) {
        $this->markTestSkipped('Empresa actual no tiene logo_impresion configurado');
    }

    expect($base64)->toStartWith('data:image/')
        ->and(base64_decode(substr($base64, strpos($base64, ',') + 1)))->toBeString();
});

it('returns a public URL when company has a logo', function () {
    $svc = app(PdfGeneratorService::class);

    $url = $svc->obtenerLogoUrl();

    if ($url === null) {
        $this->markTestSkipped('Empresa actual no tiene logo configurado');
    }

    expect($url)->toBeString();
});

it('cleans up old temporary PDFs', function () {
    $svc = app(PdfGeneratorService::class);

    // Create an old file (manually timestamp it)
    $dir = $svc->directorioTmp();
    $oldFile = $dir.'/old-test.pdf';
    file_put_contents($oldFile, '%PDF-1.4 fake');
    touch($oldFile, time() - 86400 * 2);  // 2 days ago

    $eliminados = $svc->limpiarTemporales(24);

    expect(file_exists($oldFile))->toBeFalse()
        ->and($eliminados)->toBeGreaterThanOrEqual(1);
});

it('creates tmp dir if it does not exist', function () {
    $svc = app(PdfGeneratorService::class);

    // Should not throw
    $dir = $svc->directorioTmp();
    expect(is_dir($dir))->toBeTrue();
});
