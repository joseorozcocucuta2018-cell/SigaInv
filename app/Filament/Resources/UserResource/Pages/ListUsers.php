<?php

declare(strict_types=1);

namespace App\Filament\Resources\UserResource\Pages;

use App\Enums\UserEstado;
use App\Filament\Resources\UserResource;
use App\Models\User;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('Nuevo')];
    }

    public function getTabs(): array
    {
        $counts = User::selectRaw('estado, count(*) as total')
            ->groupBy('estado')
            ->pluck('total', 'estado');

        return [
            'todos' => Tab::make('Todos')
                ->icon('heroicon-m-list-bullet')
                ->badge($counts->sum()),
            'pendiente' => Tab::make('Pendiente')
                ->icon(UserEstado::PENDIENTE->icon())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('estado', UserEstado::PENDIENTE))
                ->badge($counts->get(UserEstado::PENDIENTE->value) ?? 0)
                ->badgeColor(UserEstado::PENDIENTE->color()),
            'activo' => Tab::make('Activo')
                ->icon(UserEstado::ACTIVO->icon())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('estado', UserEstado::ACTIVO))
                ->badge($counts->get(UserEstado::ACTIVO->value) ?? 0)
                ->badgeColor(UserEstado::ACTIVO->color()),
            'inactivo' => Tab::make('Inactivo')
                ->icon(UserEstado::INACTIVO->icon())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('estado', UserEstado::INACTIVO))
                ->badge($counts->get(UserEstado::INACTIVO->value) ?? 0)
                ->badgeColor(UserEstado::INACTIVO->color()),
            'bloqueado' => Tab::make('Bloqueado')
                ->icon(UserEstado::BLOQUEADO->icon())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('estado', UserEstado::BLOQUEADO))
                ->badge($counts->get(UserEstado::BLOQUEADO->value) ?? 0)
                ->badgeColor(UserEstado::BLOQUEADO->color()),
        ];
    }
}
