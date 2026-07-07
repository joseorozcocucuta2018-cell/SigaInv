<?php

declare(strict_types=1);

namespace App\Filament\Resources\Transformacions\Pages;

use App\Enums\TransformacionEstado;
use App\Filament\Resources\Transformacions\TransformacionResource;
use App\Models\Transformacion;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListTransformacions extends ListRecords
{
    protected static string $resource = TransformacionResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('Nuevo')];
    }

    public function getTabs(): array
    {
        $counts = Transformacion::selectRaw('estado, count(*) as total')
            ->groupBy('estado')
            ->pluck('total', 'estado');

        return [
            'todas' => Tab::make('Todas')
                ->icon('heroicon-m-list-bullet'),
            'borrador' => Tab::make('Borrador')
                ->icon(TransformacionEstado::BORRADOR->icon())
                ->badgeColor(TransformacionEstado::BORRADOR->color())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('estado', TransformacionEstado::BORRADOR))
                ->badge($counts['borrador'] ?? 0),
            'confirmada' => Tab::make('Confirmada')
                ->icon(TransformacionEstado::CONFIRMADA->icon())
                ->badgeColor(TransformacionEstado::CONFIRMADA->color())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('estado', TransformacionEstado::CONFIRMADA))
                ->badge($counts['confirmada'] ?? 0),
            'revertida' => Tab::make('Revertida')
                ->icon(TransformacionEstado::REVERTIDA->icon())
                ->badgeColor(TransformacionEstado::REVERTIDA->color())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('estado', TransformacionEstado::REVERTIDA))
                ->badge($counts['revertida'] ?? 0),
        ];
    }
}
