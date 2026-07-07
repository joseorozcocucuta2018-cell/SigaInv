<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\PdfGeneratorService;
use Illuminate\Console\Command;

class LimpiarPdfTemporales extends Command
{
    protected $signature = 'pdf:limpiar-temporales
                            {--horas=24 : Archivos con más de N horas se eliminan}';

    protected $description = 'Elimina PDFs temporales en storage/app/tmp/pdf mayores a N horas (default 24h)';

    public function handle(PdfGeneratorService $service): int
    {
        $horas = (int) $this->option('horas');
        $eliminados = $service->limpiarTemporales($horas);

        $this->info("Eliminados: {$eliminados} archivo(s) PDF con más de {$horas}h en {$service->directorioTmp()}");

        return self::SUCCESS;
    }
}
