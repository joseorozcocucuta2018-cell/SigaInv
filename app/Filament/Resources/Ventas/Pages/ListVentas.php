<?php

declare(strict_types=1);

namespace App\Filament\Resources\Ventas\Pages;

use App\Enums\VentaEstado;
use App\Filament\Resources\Ventas\VentaResource;
use App\Filament\Resources\Ventas\Widgets\VentasStatsWidget;
use App\Models\Venta;
use App\Services\NumeracionService;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListVentas extends ListRecords
{
    protected static string $resource = VentaResource::class;

    protected function getHeaderActions(): array
    {
        if (! NumeracionService::tieneNumeracionActiva('venta')) {
            return [];
        }

        return [CreateAction::make()->label('Nuevo')];
    }

    protected function getHeaderWidgets(): array
    {
        return [VentasStatsWidget::class];
    }

    public function getTabs(): array
    {
        $counts = Venta::selectRaw('estado, count(*) as total')
            ->groupBy('estado')
            ->pluck('total', 'estado');

        return [
            'todas' => Tab::make('Todas')->icon('heroicon-m-list-bullet')->badge($counts->sum()),
            'borrador' => Tab::make('Borrador')->icon(VentaEstado::BORRADOR->icon())->badgeColor(VentaEstado::BORRADOR->color())->badge($counts['borrador'] ?? 0)->modifyQueryUsing(fn (Builder $q) => $q->where('estado', VentaEstado::BORRADOR)),
            'confirmada' => Tab::make('Confirmada')->icon(VentaEstado::CONFIRMADA->icon())->badgeColor(VentaEstado::CONFIRMADA->color())->badge($counts['confirmada'] ?? 0)->modifyQueryUsing(fn (Builder $q) => $q->where('estado', VentaEstado::CONFIRMADA)),
            'pagada' => Tab::make('Pagada')->icon(VentaEstado::PAGADA->icon())->badgeColor(VentaEstado::PAGADA->color())->badge($counts['pagada'] ?? 0)->modifyQueryUsing(fn (Builder $q) => $q->where('estado', VentaEstado::PAGADA)),
            'anulada' => Tab::make('Anulada')->icon(VentaEstado::ANULADA->icon())->badgeColor(VentaEstado::ANULADA->color())->badge($counts['anulada'] ?? 0)->modifyQueryUsing(fn (Builder $q) => $q->where('estado', VentaEstado::ANULADA)),
        ];
    }
}
