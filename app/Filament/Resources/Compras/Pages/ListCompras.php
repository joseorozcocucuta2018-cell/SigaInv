<?php

declare(strict_types=1);

namespace App\Filament\Resources\Compras\Pages;

use App\Enums\CompraEstado;
use App\Filament\Resources\Compras\CompraResource;
use App\Filament\Resources\Compras\Widgets\ComprasStatsWidget;
use App\Models\Compra;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListCompras extends ListRecords
{
    protected static string $resource = CompraResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Registrar'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [ComprasStatsWidget::class];
    }

    public function getTabs(): array
    {
        $counts = Compra::selectRaw('estado, count(*) as total')
            ->groupBy('estado')
            ->pluck('total', 'estado');

        return [
            'todas' => Tab::make('Todas')
                ->icon('heroicon-m-list-bullet')
                ->badge($counts->sum()),
            'borrador' => Tab::make('Borrador')
                ->icon(CompraEstado::BORRADOR->icon())
                ->badge($counts->get(CompraEstado::BORRADOR->value, 0))
                ->query(fn (Builder $query) => $query->where('estado', CompraEstado::BORRADOR)),
            'registrada' => Tab::make('Registrada')
                ->icon(CompraEstado::REGISTRADA->icon())
                ->badge($counts->get(CompraEstado::REGISTRADA->value, 0))
                ->query(fn (Builder $query) => $query->where('estado', CompraEstado::REGISTRADA)),
            'pendiente' => Tab::make('Pendiente')
                ->icon(CompraEstado::PENDIENTE->icon())
                ->badge($counts->get(CompraEstado::PENDIENTE->value, 0))
                ->query(fn (Builder $query) => $query->where('estado', CompraEstado::PENDIENTE)),
            'pagada' => Tab::make('Pagada')
                ->icon(CompraEstado::PAGADA->icon())
                ->badge($counts->get(CompraEstado::PAGADA->value, 0))
                ->query(fn (Builder $query) => $query->where('estado', CompraEstado::PAGADA)),
            'anulada' => Tab::make('Anulada')
                ->icon(CompraEstado::ANULADA->icon())
                ->badge($counts->get(CompraEstado::ANULADA->value, 0))
                ->query(fn (Builder $query) => $query->where('estado', CompraEstado::ANULADA)),
        ];
    }
}
