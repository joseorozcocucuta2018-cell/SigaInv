<?php

declare(strict_types=1);

namespace App\Filament\Resources\AjusteInventarios\Pages;

use App\Enums\AjusteEstado;
use App\Filament\Resources\AjusteInventarios\AjusteInventarioResource;
use App\Models\AjusteInventario;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListAjusteInventarios extends ListRecords
{
    protected static string $resource = AjusteInventarioResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('Nuevo')];
    }

    public function getTabs(): array
    {
        $counts = AjusteInventario::selectRaw('estado, count(*) as total')
            ->groupBy('estado')
            ->pluck('total', 'estado');

        return [
            'todos' => Tab::make('Todos')
                ->icon('heroicon-m-list-bullet')
                ->badge($counts->sum()),
            'borrador' => Tab::make('Borrador')
                ->icon('heroicon-m-clock')
                ->badge($counts[AjusteEstado::BORRADOR->value] ?? 0)
                ->modifyQueryUsing(fn (Builder $query) => $query->where('estado', AjusteEstado::BORRADOR)),
            'confirmado' => Tab::make('Confirmado')
                ->icon('heroicon-m-check-circle')
                ->badge($counts[AjusteEstado::CONFIRMADO->value] ?? 0)
                ->modifyQueryUsing(fn (Builder $query) => $query->where('estado', AjusteEstado::CONFIRMADO)),
            'anulados' => Tab::make('Anulados')
                ->icon('heroicon-m-x-circle')
                ->badge($counts[AjusteEstado::ANULADO->value] ?? 0)
                ->modifyQueryUsing(fn (Builder $query) => $query->where('estado', AjusteEstado::ANULADO)),
        ];
    }
}
