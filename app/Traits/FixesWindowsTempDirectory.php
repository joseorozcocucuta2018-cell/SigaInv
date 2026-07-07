<?php

declare(strict_types=1);

namespace App\Traits;

use OpenSpout\Writer\XLSX\Options;

trait FixesWindowsTempDirectory
{
    public function getXlsxWriterOptions(): ?Options
    {
        $options = new Options;

        $tempPath = storage_path('app/temp');

        if (! file_exists($tempPath)) {
            mkdir($tempPath, 0775, true);
        }

        $options->setTempFolder($tempPath);

        return $options;
    }
}
