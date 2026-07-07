<?php

declare(strict_types=1);

namespace App\Traits;

use App\Mail\DocumentoPdfMail;
use App\Models\Cliente;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Trait para acciones "Guardar e Imprimir" / "Guardar y Enviar" en CreateRecord
 * de documentos. Centraliza el patrón de flags pendingPrint/pendingEmail y el
 * afterCreate que dispara PDF/email.
 *
 * Uso:
 *   class CreateVenta extends CreateRecord
 *   {
 *       use HasDocumentCreateActions;
 *
 *       protected function getFormActions(): array
 *       {
 *           return [
 *               $this->getCreateFormAction(),
 *               $this->getSaveAndPrintAction('pdf.venta'),
 *               $this->getSaveAndEmailAction('venta', 'la venta'),
 *               $this->getCancelFormAction(),
 *           ];
 *       }
 *
 *       protected function afterCreate(): void
 *       {
 *           $this->handleDocumentCreated('pdf.venta', 'venta', [
 *               'cliente.ciudad', 'bodega', 'detalles.producto', 'detalles.impuesto',
 *               'usuario', 'cotizacion', 'remision',
 *           ]);
 *       }
 *
 *       // Hook opcional para lógica post-email (ej. Cotizacion -> ENVIADA)
 *       protected function onEmailSent(): void
 *       {
 *           if ($this->record->estado?->value === CotizacionEstado::PENDIENTE->value) {
 *               CotizacionService::cambiarEstado($this->record, CotizacionEstado::ENVIADA);
 *           }
 *       }
 *   }
 */
trait HasDocumentCreateActions
{
    public ?string $pendingEmail = null;

    public bool $pendingPrint = false;

    protected function getSaveAndPrintAction(string $routeName): Action
    {
        return Action::make('saveAndPrint')
            ->label('Guardar e Imprimir')
            ->icon('heroicon-o-printer')
            ->color('gray')
            ->action(function () use ($routeName) {
                $this->pendingPrint = $routeName;
                $this->create();
            });
    }

    protected function getSaveAndEmailAction(string $entity, string $entityLabel): Action
    {
        return Action::make('saveAndEmail')
            ->label('Guardar y Enviar correo')
            ->icon('heroicon-o-envelope')
            ->color('info')
            ->modalHeading('Enviar por correo')
            ->modalDescription("Se guardará {$entityLabel} y se enviará (PDF adjunto) al correo indicado.")
            ->modalSubmitActionLabel('Guardar y Enviar')
            ->visible(function (Get $get) {
                $clienteId = $get('cliente_id');
                if (empty($clienteId)) {
                    return false;
                }

                return ! empty(Cliente::find($clienteId)?->email);
            })
            ->form([
                TextInput::make('email_destino')
                    ->label('Correo del destinatario')
                    ->email()
                    ->required()
                    ->default(fn (Get $get) => Cliente::find($get('cliente_id'))?->email)
                    ->placeholder('correo@ejemplo.com'),
            ])
            ->action(function (array $data) {
                $this->pendingEmail = $data['email_destino'];
                $this->create();
            });
    }

    protected function handleDocumentCreated(string $pdfRoute, string $entity, array $loadRelations = []): void
    {
        if ($this->pendingPrint) {
            $url = route($pdfRoute, $this->record);
            $this->js("window.open({$this->jsString($url)}, {$this->jsString('_blank')})");
        }

        if ($this->pendingEmail) {
            if (! empty($loadRelations)) {
                $this->record->load($loadRelations);
            }

            try {
                Mail::to($this->pendingEmail)->send(new DocumentoPdfMail($this->record, $entity));
                $this->onEmailSent();

                Notification::make()
                    ->success()
                    ->title('Correo enviado')
                    ->body("Enviado a {$this->pendingEmail} con PDF adjunto")
                    ->send();
            } catch (\Throwable $e) {
                Log::warning("Error enviando {$entity} por correo: ".$e->getMessage());
                Notification::make()
                    ->danger()
                    ->title('Error al enviar')
                    ->body($e->getMessage())
                    ->send();
            }
        }
    }

    protected function onEmailSent(): void
    {
        // Hook vacío — sobrescribir en subclases para lógica post-envío
    }

    private function jsString(string $value): string
    {
        return json_encode($value, JSON_THROW_ON_ERROR);
    }
}
