<?php

declare(strict_types=1);

namespace App\Traits;

use App\Mail\DocumentoPdfMail;
use Closure;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Trait para acciones "Enviar por correo" / "Reenviar" en páginas de documentos.
 * Usa Mail facade (DocumentoPdfMail adjunta el PDF si se pasa ruta; aquí no se
 * adjunta porque el email es solo el resumen HTML — el PDF se descarga con PrintAction).
 *
 * Uso en Edit/View de Venta/Remision/Cotizacion:
 *   use SendsDocumentEmail;
 *
 *   protected function getHeaderActions(): array
 *   {
 *       return [
 *           $this->makeEnviarCorreoAction('venta', [...]),
 *           $this->makeReenviarAction('venta', 'la venta', [...]),
 *       ];
 *   }
 */
trait SendsDocumentEmail
{
    protected function makeEnviarCorreoAction(
        string $tipo,
        array $relations = [],
        ?string $label = null,
        ?string $description = null,
        ?Closure $afterSend = null,
    ): Action {
        $label ??= 'Enviar por correo';
        $description ??= 'Se enviará el documento (PDF adjunto) al correo indicado.';

        return Action::make('enviarCorreo')
            ->label($label)
            ->icon('heroicon-o-envelope')
            ->color('info')
            ->modalHeading('Enviar por correo')
            ->modalDescription($description)
            ->modalSubmitActionLabel('Enviar')
            ->form([
                TextInput::make('email_destino')
                    ->label('Correo del destinatario')
                    ->email()
                    ->required()
                    ->default(fn () => $this->record->cliente?->email)
                    ->placeholder('correo@ejemplo.com'),
            ])
            ->action(function (array $data) use ($tipo, $relations, $afterSend) {
                try {
                    $this->record->load($relations);
                    Mail::to($data['email_destino'])->send(new DocumentoPdfMail($this->record, $tipo));

                    if ($afterSend !== null) {
                        $afterSend($data);
                    }

                    Notification::make()
                        ->success()
                        ->title('Correo enviado')
                        ->body("Enviado a {$data['email_destino']} con PDF adjunto")
                        ->send();
                } catch (\Throwable $e) {
                    Log::warning("Error enviando {$tipo} por correo: ".$e->getMessage());
                    Notification::make()
                        ->danger()
                        ->title('Error al enviar')
                        ->body($e->getMessage())
                        ->send();
                }
            });
    }

    /**
     * Acción "Reenviar por correo" — visible solo si el cliente tiene email.
     * Idéntica a makeEnviarCorreoAction pero con label y validación de email
     * del cliente (no se muestra el botón si no hay email).
     */
    protected function makeReenviarAction(
        string $tipo,
        string $entityLabel,
        array $relations = [],
    ): Action {
        return Action::make('reenviar')
            ->label('Reenviar por correo')
            ->icon('heroicon-o-envelope')
            ->color('info')
            ->visible(fn () => ! empty($this->record->cliente?->email))
            ->modalHeading('Reenviar por correo')
            ->modalDescription("Se reenviará {$entityLabel} con PDF adjunto al correo del cliente (o uno nuevo).")
            ->modalSubmitActionLabel('Reenviar')
            ->form([
                TextInput::make('email_destino')
                    ->label('Correo del destinatario')
                    ->email()
                    ->required()
                    ->default(fn () => $this->record->cliente?->email)
                    ->placeholder('correo@ejemplo.com'),
            ])
            ->action(function (array $data) use ($tipo, $relations) {
                try {
                    $this->record->load($relations);
                    Mail::to($data['email_destino'])->send(new DocumentoPdfMail($this->record, $tipo));

                    Notification::make()
                        ->success()
                        ->title('Correo reenviado')
                        ->body("Enviado a {$data['email_destino']} con PDF adjunto")
                        ->send();
                } catch (\Throwable $e) {
                    Log::warning("Error reenviando {$tipo}: ".$e->getMessage());
                    Notification::make()
                        ->danger()
                        ->title('Error al reenviar')
                        ->body($e->getMessage())
                        ->send();
                }
            });
    }
}
