<?php

declare(strict_types=1);

namespace App\Filament\Resources\Transformacions\Pages;

use App\Filament\Resources\Transformacions\TransformacionResource;
use App\Models\FormulaTransformacion;
use App\Services\TransformacionService;
use App\Traits\FiltersEmptyRepeaterRows;
use Exception;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateTransformacion extends CreateRecord
{
    use FiltersEmptyRepeaterRows;

    protected static string $resource = TransformacionResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['usuario_id'] = Auth::id();
        $this->filterEmptyRepeaterRows($data);

        return $data;
    }

    protected function afterCreate(): void
    {
        if ($this->record->formula_transformacion_id) {
            try {
                $service = app(TransformacionService::class);
                $formula = FormulaTransformacion::findOrFail($this->record->formula_transformacion_id);
                $cantidadAProducir = (float) ($this->record->cantidad_a_producir ?? 1);

                $service->applyFormulaToTransformacion($this->record, $formula, $cantidadAProducir);

                Notification::make()
                    ->title('Fórmula aplicada')
                    ->body("Se han generado automáticamente los componentes de la fórmula '{$formula->producto_final_nombre}'")
                    ->success()
                    ->send();
            } catch (Exception $e) {
                Notification::make()
                    ->title('Error al aplicar fórmula')
                    ->body($e->getMessage())
                    ->danger()
                    ->send();
            }
        }
    }
}
