<?php

declare(strict_types=1);

namespace App\Filament\Resources\Productos\Pages;

use App\Filament\Resources\Productos\ProductoResource;
use App\Services\PrecioService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Notification;

class EditProducto extends EditRecord
{
    protected static string $resource = ProductoResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('calcular_precio')
                ->label('Calcular Precio Venta')
                ->icon('heroicon-o-calculator')
                ->action(function () {
                    $record = $this->getRecord();

                    if ($record->precio_compra > 0) {
                        $nuevoPrecio = app(PrecioService::class)->calcularPrecioConMargen((float) $record->precio_compra);
                        $record->update(['precio_venta' => $nuevoPrecio]);

                        Notification::make()
                            ->title('Precio actualizado')
                            ->body('Nuevo precio de venta: $'.number_format($nuevoPrecio, 0, ',', '.'))
                            ->success()
                            ->send();

                        $this->refreshFormData(['precio_venta']);
                    } else {
                        Notification::make()
                            ->title('Error')
                            ->body('El precio de compra debe ser mayor a 0')
                            ->danger()
                            ->send();
                    }
                })
                ->visible(fn () => $this->getRecord()->precio_compra > 0),
            DeleteAction::make(),
        ];
    }
}
