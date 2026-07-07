<?php

declare(strict_types=1);

namespace App\Filament\Resources\Traslados\Pages;

use App\Enums\TrasladoEstado;
use App\Filament\Resources\Traslados\TrasladoResource;
use App\Models\Traslado;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListTraslados extends ListRecords
{
    protected static string $resource = TrasladoResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('Nuevo')];
    }

    public function getTabs(): array
    {
        $counts = Traslado::selectRaw('estado, count(*) as total')
            ->groupBy('estado')
            ->pluck('total', 'estado');

        return [
            'todos' => Tab::make('Todos')
                ->icon('heroicon-m-list-bullet')
                ->badge($counts->sum()),
            'borrador' => Tab::make('Borrador')
                ->icon(TrasladoEstado::BORRADOR->icon())
                ->badgeColor(TrasladoEstado::BORRADOR->color())
                ->badge($counts['borrador'] ?? 0)
                ->modifyQueryUsing(fn (Builder $q) => $q->where('estado', TrasladoEstado::BORRADOR)),

            'confirmada' => Tab::make('Confirmada')
                ->icon(TrasladoEstado::CONFIRMADA->icon())
                ->badgeColor(TrasladoEstado::CONFIRMADA->color())
                ->badge($counts['confirmada'] ?? 0)
                ->modifyQueryUsing(fn (Builder $q) => $q->where('estado', TrasladoEstado::CONFIRMADA)),
        ];
    }
}
