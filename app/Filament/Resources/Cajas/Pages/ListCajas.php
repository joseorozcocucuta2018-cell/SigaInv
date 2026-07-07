<?php

declare(strict_types=1);

namespace App\Filament\Resources\Cajas\Pages;

use App\Enums\CajaEstado;
use App\Filament\Resources\Cajas\CajaResource;
use App\Models\Caja;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListCajas extends ListRecords
{
    protected static string $resource = CajaResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('Nuevo')];
    }

    public function getTabs(): array
    {
        $counts = Caja::selectRaw('estado, count(*) as total')
            ->groupBy('estado')
            ->pluck('total', 'estado');

        return [
            'todos' => Tab::make('Todos')
                ->icon('heroicon-m-list-bullet')
                ->badge($counts->sum()),
            'activos' => Tab::make('Activas')
                ->icon(CajaEstado::ACTIVA->icon())
                ->badgeColor(CajaEstado::ACTIVA->color())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('estado', CajaEstado::ACTIVA->value))
                ->badge($counts->get(CajaEstado::ACTIVA->value) ?? 0),
            'inactivos' => Tab::make('Inactivas')
                ->icon(CajaEstado::INACTIVA->icon())
                ->badgeColor(CajaEstado::INACTIVA->color())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('estado', CajaEstado::INACTIVA->value))
                ->badge($counts->get(CajaEstado::INACTIVA->value) ?? 0),
        ];
    }
}
