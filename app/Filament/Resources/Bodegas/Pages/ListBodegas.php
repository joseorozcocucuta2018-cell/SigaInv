<?php

declare(strict_types=1);

namespace App\Filament\Resources\Bodegas\Pages;

use App\Enums\BodegaEstado;
use App\Filament\Resources\Bodegas\BodegaResource;
use App\Models\Bodega;
use App\Models\Empresa;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListBodegas extends ListRecords
{
    protected static string $resource = BodegaResource::class;

    protected function getHeaderActions(): array
    {
        if (Empresa::usaUnaSolaBodega()) {
            return [];
        }

        return [CreateAction::make()->label('Nuevo')];
    }

    public function getTabs(): array
    {
        $counts = Bodega::selectRaw('estado, count(*) as total')
            ->groupBy('estado')
            ->pluck('total', 'estado');

        return [
            'todos' => Tab::make('Todos')
                ->icon('heroicon-m-list-bullet')
                ->badge($counts->sum()),
            'activos' => Tab::make('Activos')
                ->icon(BodegaEstado::ACTIVO->icon())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('estado', BodegaEstado::ACTIVO))
                ->badge($counts->get(BodegaEstado::ACTIVO->value) ?? 0),
            'inactivos' => Tab::make('Inactivos')
                ->icon(BodegaEstado::INACTIVO->icon())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('estado', BodegaEstado::INACTIVO))
                ->badge($counts->get(BodegaEstado::INACTIVO->value) ?? 0),
        ];
    }
}
