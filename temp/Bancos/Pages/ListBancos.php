<?php

namespace App\Filament\Resources\Bancos\Pages;

use App\Enums\BancoEstado;
use App\Filament\Resources\Bancos\BancoResource;
use App\Models\Banco;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListBancos extends ListRecords
{
    protected static string $resource = BancoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Nuevo'),
        ];
    }

    public function getTabs(): array
    {
        $counts = Banco::selectRaw('estado, count(*) as total')
            ->groupBy('estado')
            ->pluck('total', 'estado');

        return [
            'todos' => Tab::make('Todos')
                ->icon('heroicon-m-list-bullet')
                ->badge($counts->sum()),
            'activos' => Tab::make('Activas')
                ->icon(BancoEstado::ACTIVO->icon())
                ->badgeColor(BancoEstado::ACTIVO->color())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('estado', BancoEstado::ACTIVO))
                ->badge($counts->get(BancoEstado::ACTIVO->value) ?? 0),
            'inactivos' => Tab::make('Inactivas')
                ->icon(BancoEstado::INACTIVO->icon())
                ->badgeColor(BancoEstado::INACTIVO->color())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('estado', BancoEstado::INACTIVO))
                ->badge($counts->get(BancoEstado::INACTIVO->value) ?? 0),
        ];
    }
}
