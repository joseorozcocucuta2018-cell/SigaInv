<?php

declare(strict_types=1);

namespace App\Filament\Resources\Devoluciones\Pages;

use App\Enums\DevolucionEstado;
use App\Filament\Resources\Devoluciones\DevolucionResource;
use App\Models\Devolucion;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListDevoluciones extends ListRecords
{
    protected static string $resource = DevolucionResource::class;

    public function getTabs(): array
    {
        $counts = Devolucion::selectRaw('estado, count(*) as total')
            ->groupBy('estado')
            ->pluck('total', 'estado');

        return [
            'todas' => Tab::make('Todas')->icon('heroicon-m-list-bullet')->badge($counts->sum()),
            'borrador' => Tab::make('Borrador')->icon(DevolucionEstado::BORRADOR->icon())->badgeColor(DevolucionEstado::BORRADOR->color())->badge($counts['borrador'] ?? 0)->modifyQueryUsing(fn (Builder $q) => $q->where('estado', DevolucionEstado::BORRADOR)),
            'confirmada' => Tab::make('Confirmada')->icon(DevolucionEstado::CONFIRMADA->icon())->badgeColor(DevolucionEstado::CONFIRMADA->color())->badge($counts['confirmada'] ?? 0)->modifyQueryUsing(fn (Builder $q) => $q->where('estado', DevolucionEstado::CONFIRMADA)),
            'anulada' => Tab::make('Anulada')->icon(DevolucionEstado::ANULADA->icon())->badgeColor(DevolucionEstado::ANULADA->color())->badge($counts['anulada'] ?? 0)->modifyQueryUsing(fn (Builder $q) => $q->where('estado', DevolucionEstado::ANULADA)),
        ];
    }
}
