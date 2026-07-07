<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Models\AjusteInventario;
use App\Models\MovimientoInventario;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ReportePerdidas extends Page implements HasForms, HasTable
{
    use InteractsWithForms, InteractsWithTable;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-exclamation-triangle';

    protected static \UnitEnum|string|null $navigationGroup = 'Reportes';

    protected static ?string $navigationLabel = 'Pérdidas';

    protected static ?string $title = 'Reporte de Pérdidas (Merma, Daño, Robo)';

    protected static ?int $navigationSort = 30;

    protected string $view = 'filament.pages.reporte-perdidas';

    public ?string $fecha_desde = null;

    public ?string $fecha_hasta = null;

    public static function canAccess(): bool
    {
        return Auth::user()?->can('reporte.ver') ?? false;
    }

    protected function getForms(): array
    {
        return ['filtersForm'];
    }

    public function filtersForm(Schema $schema): Schema
    {
        $now = now();

        return $schema
            ->schema([
                DatePicker::make('fecha_desde')
                    ->label('Desde')
                    ->default($now->copy()->startOfMonth())
                    ->live(),
                DatePicker::make('fecha_hasta')
                    ->label('Hasta')
                    ->default($now->copy()->endOfMonth())
                    ->live(),
            ]);
    }

    public function table(Table $table): Table
    {
        $ajusteIds = AjusteInventario::whereIn('motivo', ['merma', 'daño', 'robo'])->select('id');

        return $table
            ->query(
                MovimientoInventario::query()
                    ->whereIn('tipo_movimiento', ['ajuste_negativo'])
                    ->where('documento_tipo', 'ajuste_inventario')
                    ->whereIn('documento_id', $ajusteIds)
                    ->when($this->fecha_desde, fn (Builder $q) => $q->whereDate('created_at', '>=', $this->fecha_desde))
                    ->when($this->fecha_hasta, fn (Builder $q) => $q->whereDate('created_at', '<=', $this->fecha_hasta))
            )
            ->columns([
                TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('producto.nombre')
                    ->label('Producto')
                    ->searchable(),
                TextColumn::make('documento_id')
                    ->label('Motivo')
                    ->formatStateUsing(function ($state): string {
                        $ajuste = AjusteInventario::find($state);

                        return $ajuste?->motivo?->label() ?? '—';
                    })
                    ->badge()
                    ->color(function ($state): string {
                        $ajuste = AjusteInventario::find($state);

                        return match ($ajuste?->motivo?->value) {
                            'merma' => 'warning',
                            'daño' => 'danger',
                            'robo' => 'danger',
                            default => 'gray',
                        };
                    }),
                TextColumn::make('cantidad')
                    ->label('Cantidad')
                    ->numeric(3)
                    ->alignRight(),
                TextColumn::make('costo_unitario')
                    ->label('Costo Unit.')
                    ->currency()
                    ->alignRight(),
            ])
            ->defaultSort('created_at', 'desc')
            ->defaultPaginationPageOption(25);
    }

    public function getTotales(): array
    {
        $ajusteIds = AjusteInventario::whereIn('motivo', ['merma', 'daño', 'robo'])->pluck('id');

        $rows = MovimientoInventario::whereIn('tipo_movimiento', ['ajuste_negativo'])
            ->where('documento_tipo', 'ajuste_inventario')
            ->whereIn('documento_id', $ajusteIds)
            ->when($this->fecha_desde, fn (Builder $q) => $q->whereDate('created_at', '>=', $this->fecha_desde))
            ->when($this->fecha_hasta, fn (Builder $q) => $q->whereDate('created_at', '<=', $this->fecha_hasta))
            ->get();

        $totalPerdida = 0;
        $totalCantidad = 0;
        $porMotivo = [];

        foreach ($rows as $row) {
            $costo = (float) ($row->costo_unitario ?? 0);
            $cantidad = (float) ($row->cantidad ?? 0);
            $totalPerdida += $costo * $cantidad;
            $totalCantidad += $cantidad;

            $ajuste = AjusteInventario::find($row->documento_id);
            $motivo = $ajuste?->motivo?->value ?? 'otro';
            $porMotivo[$motivo] = ($porMotivo[$motivo] ?? 0) + ($costo * $cantidad);
        }

        return [
            'cantidad' => $totalCantidad,
            'total_perdida' => $totalPerdida,
            'total_merma' => $porMotivo['merma'] ?? 0,
            'total_daño' => $porMotivo['daño'] ?? 0,
            'total_robo' => $porMotivo['robo'] ?? 0,
        ];
    }
}
