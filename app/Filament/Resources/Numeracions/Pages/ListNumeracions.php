<?php

declare(strict_types=1);

namespace App\Filament\Resources\Numeracions\Pages;

use App\Enums\NumeracionEstado;
use App\Filament\Resources\Numeracions\NumeracionResource;
use App\Models\Numeracion;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListNumeracions extends ListRecords
{
    protected static string $resource = NumeracionResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('Nuevo')];
    }

    public function getTabs(): array
    {

        $counts = Numeracion::selectRaw('estado, count(*) as total')
            ->groupBy('estado')
            ->pluck('total', 'estado');

        return [
            'todos' => Tab::make('Todos')
                ->icon('heroicon-m-list-bullet')
                ->badge($counts->sum()),
            'activos' => Tab::make('Activos')
                ->icon(NumeracionEstado::ACTIVO->icon())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('estado', NumeracionEstado::ACTIVO))
                ->badge($counts->get(NumeracionEstado::ACTIVO->value) ?? 0),
            'inactivos' => Tab::make('Inactivos')
                ->icon(NumeracionEstado::INACTIVO->icon())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('estado', NumeracionEstado::INACTIVO))
                ->badge($counts->get(NumeracionEstado::INACTIVO->value) ?? 0),
        ];
    }
}
