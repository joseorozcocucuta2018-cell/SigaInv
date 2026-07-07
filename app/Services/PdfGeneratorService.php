<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Empresa;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Servicio para generación de PDFs con mPDF.
 *
 * Estrategia logos:
 *  - PDF (mPDF): Base64 — incrustado, sin dependencia de red.
 *  - Email HTML: URL pública — vía cascada logo_impresion → logo.
 *
 * On-the-fly: los PDFs se generan, se envían, y se eliminan automáticamente
 * después de 24h (ver comando limpiarTemporales).
 */
class PdfGeneratorService
{
    public function __construct() {}

    /**
     * Generar un PDF a partir de una vista Blade.
     *
     * @param  string  $vista  Ruta Blade (ej. 'pdfs.ventas.factura-carta')
     * @param  array<string, mixed>  $datos  Variables para la vista
     * @param  array<string, mixed>  $opciones  formato, orientacion, margenes, logo_base64, nombre
     * @return string Ruta absoluta al archivo PDF temporal
     */
    public function generarPdfDesdeVista(string $vista, array $datos = [], array $opciones = []): string
    {
        $datos['logoBase64'] = $datos['logoBase64'] ?? $opciones['logo_base64'] ?? $this->obtenerLogoBase64();

        $configMpdf = [
            'format' => $this->formatoMpdf($opciones['formato'] ?? 'carta'),
            'orientation' => $opciones['orientacion'] ?? 'portrait',
            'margin_left' => (int) ($opciones['margen_izquierdo'] ?? 15),
            'margin_right' => (int) ($opciones['margen_derecho'] ?? 15),
            'margin_top' => (int) ($opciones['margen_superior'] ?? 15),
            'margin_bottom' => (int) ($opciones['margen_inferior'] ?? 15),
        ];

        $pdf = app('laravel-mpdf')->loadView($vista, $datos, [], $configMpdf);

        $nombre = $opciones['nombre'] ?? 'documento-'.Str::ulid();
        $ruta = $this->directorioTmp().'/'.$nombre.'.pdf';

        $pdf->save($ruta);

        return $ruta;
    }

    /**
     * Convertir formato legible ('carta', 'A4') a nombre interno de mPDF.
     */
    private function formatoMpdf(string $formato): string
    {
        return match (strtolower($formato)) {
            'a4' => 'A4',
            'a5' => 'A5',
            'oficio', 'legal' => 'legal',
            default => 'letter',
        };
    }

    /**
     * Logo en base64 — incrustado en el PDF (mPDF).
     * Lee Empresa.logo_impresion del disco 'directo' y lo convierte a data URI.
     */
    public function obtenerLogoBase64(): ?string
    {
        $empresa = Empresa::actual();

        if (! $empresa?->logo_impresion) {
            return null;
        }

        $ruta = Storage::disk('directo')->path($empresa->logo_impresion);

        if (! is_file($ruta)) {
            return null;
        }

        $mime = $this->mimeDeArchivo($ruta);
        $binario = file_get_contents($ruta);

        return 'data:'.$mime.';base64,'.base64_encode($binario);
    }

    /**
     * URL pública del logo para email HTML.
     * Cascada: logo_impresion → logo → logo_url_publico.
     */
    public function obtenerLogoUrl(): ?string
    {
        $empresa = Empresa::actual();

        if (! $empresa) {
            return null;
        }

        $candidatos = array_filter([
            $empresa->logo_url_publico ?? null,
            $empresa->logo_impresion ?? null,
            $empresa->logo ?? null,
        ]);

        foreach ($candidatos as $path) {
            try {
                return Storage::disk('directo')->url($path);
            } catch (\Throwable) {
                continue;
            }
        }

        return null;
    }

    /**
     * Limpiar PDFs temporales con más de 24 horas.
     * Pensado para correr en un job/console programado.
     */
    public function limpiarTemporales(int $horas = 24): int
    {
        $dir = $this->directorioTmp();
        $limite = now()->subHours($horas)->timestamp;
        $eliminados = 0;

        if (! is_dir($dir)) {
            return 0;
        }

        foreach (File::files($dir) as $archivo) {
            if ($archivo->getMTime() < $limite) {
                File::delete($archivo->getPathname());
                $eliminados++;
            }
        }

        return $eliminados;
    }

    /**
     * Directorio temporal de PDFs.
     */
    public function directorioTmp(): string
    {
        $dir = config('pdf.temp_dir', storage_path('app/tmp/pdf'));

        if (! is_dir($dir)) {
            File::makeDirectory($dir, 0755, true);
        }

        return $dir;
    }

    private function mimeDeArchivo(string $ruta): string
    {
        $ext = strtolower(pathinfo($ruta, PATHINFO_EXTENSION));

        return match ($ext) {
            'png' => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml',
            'webp' => 'image/webp',
            default => 'image/png',
        };
    }
}
