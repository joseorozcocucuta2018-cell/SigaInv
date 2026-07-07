<?php

declare(strict_types=1);

namespace App\Filament\Actions;

use App\Services\DocumentoEmailService;
use Filament\Actions\BulkAction;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

/**
 * Factory para Bulk Action "Reenviar por correo" en tablas Filament.
 * Filtra solo los registros con cliente que tenga email y envía a cada uno
 * (batch con notificación de resumen al final).
 */
final class BulkResendEmailAction
{
    public static function make(string $tipo): BulkAction
    {
        return BulkAction::make('reenviarCorreo')
            ->label('Reenviar por correo')
            ->icon('heroicon-o-envelope')
            ->color('info')
            ->deselectRecordsAfterCompletion()
            ->modalHeading('Reenviar documentos por correo')
            ->modalDescription('Se enviarán los PDFs por correo a los clientes que tengan email registrado.')
            ->modalSubmitActionLabel('Enviar a todos')
            ->action(function (Collection $records) use ($tipo) {
                $enviados = 0;
                $omitidos = 0;
                $errores = 0;

                foreach ($records as $record) {
                    $email = $record->cliente?->email;

                    if (empty($email)) {
                        $omitidos++;

                        continue;
                    }

                    try {
                        app(DocumentoEmailService::class)->enviarDocumento($record, $tipo, $email);
                        $enviados++;
                    } catch (\Throwable $e) {
                        $errores++;
                        Log::warning("Error reenviando {$tipo} #{$record->id}: ".$e->getMessage());
                    }
                }

                Notification::make()
                    ->success()
                    ->title('Reenvío masivo completado')
                    ->body("Enviados: {$enviados} | Sin email: {$omitidos} | Errores: {$errores}")
                    ->send();
            });
    }
}
