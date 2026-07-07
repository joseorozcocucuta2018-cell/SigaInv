<?php

declare(strict_types=1);

namespace App\Filament\Resources\FormulaTransformacions\Pages;

use App\Filament\Resources\FormulaTransformacions\FormulaTransformacionResource;
use App\Enums\TransformacionTipo;
use App\Models\Marca;
use App\Models\UnidadMedida;
use App\Services\ProductoService;
use App\Traits\FiltersEmptyRepeaterRows;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateFormulaTransformacion extends CreateRecord
{
    use FiltersEmptyRepeaterRows;

    protected static string $resource = FormulaTransformacionResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['usuario_id'] = Auth::id();
        $this->filterEmptyRepeaterRows($data);

        if (! empty($data['producto_final_nombre'])) {
            $unidadMedida = UnidadMedida::where('activo', true)->first();
            $marca = Marca::firstOrCreate(['nombre' => mb_strtoupper('Marca Propia')], ['activo' => true]);
            $rawTipo = $data['tipo'] ?? null;
            $tipo = $rawTipo instanceof TransformacionTipo ? $rawTipo : TransformacionTipo::tryFrom($rawTipo ?? '');
            $categoriaId = $tipo?->categoriaSugerida()?->id;

            $productoFinal = ProductoService::crear([
                'nombre' => $data['producto_final_nombre'],
                'tipo_producto' => 'manufacturado',
                'unidad_medida_id' => $unidadMedida?->id,
                'marca_id' => $marca->id,
                'categoria_id' => $categoriaId,
            ]);

            $data['producto_final_id'] = $productoFinal->id;
        }

        return $data;
    }
}
