<?php

declare(strict_types=1);

namespace App\Filament\Resources\ConteoFisicos\Pages;

use App\Enums\ConteoFisicoEstado;
use App\Filament\Resources\ConteoFisicos\ConteoFisicoResource;
use App\Models\ConteoFisico;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListConteoFisicos extends ListRecords
{
    protected static string $resource = ConteoFisicoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Nuevo'),
        ];
    }

    public function getTabs(): array
    {
        $counts = ConteoFisico::selectRaw('estado, count(*) as total')
            ->groupBy('estado')
            ->pluck('total', 'estado');

        return [
            'todos' => Tab::make('Todos')
                ->icon('heroicon-m-list-bullet')
                ->badge($counts->sum()),
            'abiertos' => Tab::make('Abiertos')
                ->icon(ConteoFisicoEstado::ABIERTO->icon())
                ->badgeColor(ConteoFisicoEstado::ABIERTO->color())
                ->badge($counts[ConteoFisicoEstado::ABIERTO->value] ?? 0)
                ->modifyQueryUsing(fn (Builder $query) => $query->where('estado', ConteoFisicoEstado::ABIERTO)),
            'cerrados' => Tab::make('Cerrados')
                ->icon(ConteoFisicoEstado::CERRADO->icon())
                ->badgeColor(ConteoFisicoEstado::CERRADO->color())
                ->badge($counts[ConteoFisicoEstado::CERRADO->value] ?? 0)
                ->modifyQueryUsing(fn (Builder $query) => $query->where('estado', ConteoFisicoEstado::CERRADO)),
            'ajustados' => Tab::make('Ajustados')
                ->icon(ConteoFisicoEstado::AJUSTADO->icon())
                ->badgeColor(ConteoFisicoEstado::AJUSTADO->color())
                ->badge($counts[ConteoFisicoEstado::AJUSTADO->value] ?? 0)
                ->modifyQueryUsing(fn (Builder $query) => $query->where('estado', ConteoFisicoEstado::AJUSTADO)),
        ];
    }
}
