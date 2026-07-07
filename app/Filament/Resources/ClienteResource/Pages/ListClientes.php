<?php

declare(strict_types=1);

namespace App\Filament\Resources\ClienteResource\Pages;

use App\Enums\ClienteEstado;
use App\Filament\Resources\ClienteResource;
use App\Filament\Resources\ClienteResource\Widgets\ClientesStatsWidget;
use App\Models\Cliente;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListClientes extends ListRecords
{
    protected static string $resource = ClienteResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('Nuevo')];
    }

    protected function getHeaderWidgets(): array
    {
        return [ClientesStatsWidget::class];
    }

    public function getTabs(): array
    {
        $counts = Cliente::selectRaw('estado, count(*) as total')
            ->groupBy('estado')
            ->pluck('total', 'estado');

        return [
            'todos' => Tab::make('Todos')
                ->icon('heroicon-m-list-bullet')
                ->badge($counts->sum()),
            'activos' => Tab::make('Activos')
                ->icon(ClienteEstado::ACTIVO->icon())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('estado', ClienteEstado::ACTIVO))
                ->badge($counts->get(ClienteEstado::ACTIVO->value) ?? 0),
            'inactivos' => Tab::make('Inactivos')
                ->icon(ClienteEstado::INACTIVO->icon())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('estado', ClienteEstado::INACTIVO))
                ->badge($counts->get(ClienteEstado::INACTIVO->value) ?? 0),
        ];
    }
}
