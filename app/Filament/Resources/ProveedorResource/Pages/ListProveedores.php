<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProveedorResource\Pages;

use App\Enums\ProveedorEstado;
use App\Filament\Resources\ProveedorResource;
use App\Filament\Resources\ProveedorResource\Widgets\ProveedoresStatsWidget;
use App\Models\Proveedor;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListProveedores extends ListRecords
{
    protected static string $resource = ProveedorResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('Nuevo')];
    }

    protected function getHeaderWidgets(): array
    {
        return [ProveedoresStatsWidget::class];
    }

    public function getTabs(): array
    {
        $counts = Proveedor::selectRaw('estado, count(*) as total')
            ->groupBy('estado')
            ->pluck('total', 'estado');

        return [
            'todos' => Tab::make('Todos')
                ->icon('heroicon-m-list-bullet')
                ->badge($counts->sum()),
            'activos' => Tab::make('Activos')
                ->icon(ProveedorEstado::ACTIVO->icon())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('estado', ProveedorEstado::ACTIVO))
                ->badge($counts->get(ProveedorEstado::ACTIVO->value) ?? 0),
            'inactivos' => Tab::make('Inactivos')
                ->icon(ProveedorEstado::INACTIVO->icon())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('estado', ProveedorEstado::INACTIVO))
                ->badge($counts->get(ProveedorEstado::INACTIVO->value) ?? 0),
        ];
    }
}
