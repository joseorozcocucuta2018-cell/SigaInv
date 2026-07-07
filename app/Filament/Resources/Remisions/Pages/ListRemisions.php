<?php

declare(strict_types=1);

namespace App\Filament\Resources\Remisions\Pages;

use App\Enums\RemisionEstado;
use App\Filament\Resources\Remisions\RemisionResource;
use App\Filament\Resources\Remisions\Widgets\RemisionesStatsWidget;
use App\Models\Remision;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListRemisions extends ListRecords
{
    protected static string $resource = RemisionResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('Nuevo')];
    }

    protected function getHeaderWidgets(): array
    {
        return [RemisionesStatsWidget::class];
    }

    public function getTabs(): array
    {
        $counts = Remision::selectRaw('estado, count(*) as total')
            ->groupBy('estado')
            ->pluck('total', 'estado');

        return [
            'todas' => Tab::make('Todas')->icon('heroicon-m-list-bullet')->badge($counts->sum()),
            'borrador' => Tab::make('Borrador')->icon(RemisionEstado::BORRADOR->icon())->badgeColor(RemisionEstado::BORRADOR->color())->badge($counts['borrador'] ?? 0)->modifyQueryUsing(fn (Builder $q) => $q->where('estado', RemisionEstado::BORRADOR)),
            'confirmada' => Tab::make('Confirmada')->icon(RemisionEstado::CONFIRMADA->icon())->badgeColor(RemisionEstado::CONFIRMADA->color())->badge($counts['confirmada'] ?? 0)->modifyQueryUsing(fn (Builder $q) => $q->where('estado', RemisionEstado::CONFIRMADA)),
            'facturada' => Tab::make('Facturada')->icon(RemisionEstado::FACTURADA->icon())->badgeColor(RemisionEstado::FACTURADA->color())->badge($counts['facturada'] ?? 0)->modifyQueryUsing(fn (Builder $q) => $q->where('estado', RemisionEstado::FACTURADA)),
            'anulada' => Tab::make('Anulada')->icon(RemisionEstado::ANULADA->icon())->badgeColor(RemisionEstado::ANULADA->color())->badge($counts['anulada'] ?? 0)->modifyQueryUsing(fn (Builder $q) => $q->where('estado', RemisionEstado::ANULADA)),
        ];
    }
}
