<?php

declare(strict_types=1);

namespace App\Filament\Resources\Cotizacions\Pages;

use App\Enums\CotizacionEstado;
use App\Filament\Resources\Cotizacions\CotizacionResource;
use App\Filament\Resources\Cotizacions\Widgets\CotizacionesStatsWidget;
use App\Models\Cotizacion;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListCotizacions extends ListRecords
{
    protected static string $resource = CotizacionResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('Nuevo')];
    }

    protected function getHeaderWidgets(): array
    {
        return [CotizacionesStatsWidget::class];
    }

    public function getTabs(): array
    {
        $counts = Cotizacion::selectRaw('estado, count(*) as total')
            ->groupBy('estado')
            ->pluck('total', 'estado');

        return [
            'todas' => Tab::make('Todas')
                ->icon('heroicon-m-list-bullet')
                ->badge($counts->sum()),
            'pendiente' => Tab::make('Pendiente')
                ->icon(CotizacionEstado::PENDIENTE->icon())
                ->badgeColor(CotizacionEstado::PENDIENTE->color())
                ->badge($counts[CotizacionEstado::PENDIENTE->value] ?? 0)
                ->modifyQueryUsing(fn (Builder $query) => $query->where('estado', CotizacionEstado::PENDIENTE)),
            'enviada' => Tab::make('Enviada')
                ->icon(CotizacionEstado::ENVIADA->icon())
                ->badgeColor(CotizacionEstado::ENVIADA->color())
                ->badge($counts[CotizacionEstado::ENVIADA->value] ?? 0)
                ->modifyQueryUsing(fn (Builder $query) => $query->where('estado', CotizacionEstado::ENVIADA)),
            'aceptada' => Tab::make('Aceptada')
                ->icon(CotizacionEstado::ACEPTADA->icon())
                ->badgeColor(CotizacionEstado::ACEPTADA->color())
                ->badge($counts[CotizacionEstado::ACEPTADA->value] ?? 0)
                ->modifyQueryUsing(fn (Builder $query) => $query->where('estado', CotizacionEstado::ACEPTADA)),
            'rechazada' => Tab::make('Rechazada')
                ->icon(CotizacionEstado::RECHAZADA->icon())
                ->badgeColor(CotizacionEstado::RECHAZADA->color())
                ->badge($counts[CotizacionEstado::RECHAZADA->value] ?? 0)
                ->modifyQueryUsing(fn (Builder $query) => $query->where('estado', CotizacionEstado::RECHAZADA)),
            'vencida' => Tab::make('Vencida')
                ->icon(CotizacionEstado::VENCIDA->icon())
                ->badgeColor(CotizacionEstado::VENCIDA->color())
                ->badge($counts[CotizacionEstado::VENCIDA->value] ?? 0)
                ->modifyQueryUsing(fn (Builder $query) => $query->where('estado', CotizacionEstado::VENCIDA)),
        ];
    }
}
