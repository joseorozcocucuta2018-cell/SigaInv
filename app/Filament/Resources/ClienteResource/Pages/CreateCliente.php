<?php

declare(strict_types=1);

namespace App\Filament\Resources\ClienteResource\Pages;

use App\Enums\PortalAccesoEnum;
use App\Filament\Resources\ClienteResource;
use App\Models\Cliente;
use App\Services\PortalAccesoService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateCliente extends CreateRecord
{
    protected static string $resource = ClienteResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['usuario_id'] = Auth::id();
        $data['portal_acceso'] = $data['portal_acceso'] ?? PortalAccesoEnum::ACTIVO->value;

        return $data;
    }

    protected function afterCreate(): void
    {
        /** @var Cliente $cliente */
        $cliente = $this->record;
        $servicio = app(PortalAccesoService::class);

        if (! $servicio->puedeTenerAcceso($cliente)) {
            if ($cliente->portal_acceso?->value === PortalAccesoEnum::ACTIVO->value) {
                Notification::make()
                    ->title('Cliente creado sin acceso al portal')
                    ->body('El cliente no tiene un email válido, no se generó contraseña de acceso.')
                    ->warning()
                    ->send();
            }

            return;
        }

        try {
            $servicio->generarYEnviarPassword($cliente);

            Notification::make()
                ->title('Cliente creado con acceso al portal')
                ->body("Se envió una contraseña temporal a {$cliente->email}.")
                ->success()
                ->send();
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Cliente creado (sin envío de email)')
                ->body('No se pudo enviar la contraseña por email. Usa "Generar contraseña de acceso" en el header para reintentar.')
                ->warning()
                ->send();
        }
    }
}
