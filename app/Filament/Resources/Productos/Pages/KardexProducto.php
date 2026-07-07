<?php

declare(strict_types=1);

namespace App\Filament\Resources\Productos\Pages;

use App\Filament\Resources\Productos\ProductoResource;
use App\Models\Bodega;
use App\Models\MovimientoInventario;
use App\Models\Producto;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class KardexProducto extends Page implements HasForms, HasTable
{
    use InteractsWithForms, InteractsWithTable;

    protected static string $resource = ProductoResource::class;

    protected string $view = 'filament.resources.productos.pages.kardex-producto';

    protected static ?string $title = 'Kardex';

    public ?int $bodega_id = null;

    public ?string $fecha_desde = null;

    public ?string $fecha_hasta = null;

    public function mount(int|string $record): void
    {
        $this->record = Producto::findOrFail($record);
    }

    protected function getForms(): array
    {
        return ['filtersForm'];
    }

    public function filtersForm(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Select::make('bodega_id')
                    ->label('Bodega')
                    ->options(Bodega::pluck('nombre', 'id'))
                    ->placeholder('Todas las bodegas')
                    ->live(),
                DatePicker::make('fecha_desde')
                    ->label('Desde')
                    ->live(),
                DatePicker::make('fecha_hasta')
                    ->label('Hasta')
                    ->live(),
            ])
            ->columns(3);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                MovimientoInventario::query()
                    ->where('producto_id', $this->record->id)
                    ->when($this->bodega_id, fn (Builder $q) => $q->where('bodega_id', $this->bodega_id))
                    ->when($this->fecha_desde, fn (Builder $q) => $q->whereDate('fecha_movimiento', '>=', $this->fecha_desde))
                    ->when($this->fecha_hasta, fn (Builder $q) => $q->whereDate('fecha_movimiento', '<=', $this->fecha_hasta))
            )
            ->defaultSort('fecha_movimiento', 'asc')
            ->columns([
                TextColumn::make('fecha_movimiento')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('bodega.nombre')
                    ->label('Bodega')
                    ->sortable(),
                TextColumn::make('tipo_movimiento')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn (string $state): string => match (true) {
                        str_starts_with($state, 'entrada') => 'success',
                        str_starts_with($state, 'salida') => 'danger',
                        str_starts_with($state, 'reverso') => 'warning',
                        str_starts_with($state, 'ajuste_inicial') => 'success',
                        str_starts_with($state, 'ajuste_positivo') => 'success',
                        str_starts_with($state, 'ajuste_conteo') => 'info',
                        str_starts_with($state, 'ajuste_negativo') => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'entrada_compra' => 'Entrada Compra',
                        'salida_venta' => 'Salida Venta',
                        'salida_remision' => 'Salida Remisión',
                        'entrada_devolucion' => 'Entrada Devolución',
                        'salida_devolucion' => 'Salida Devolución',
                        'salida_anulacion_devolucion' => 'Anulación Devolución',
                        'traslado_entrada', 'entrada_traslado' => 'Entrada Traslado',
                        'traslado_salida', 'salida_traslado' => 'Salida Traslado',
                        'reverso_traslado' => 'Reverso Traslado',
                        'reverso_anulacion' => 'Reverso Anulación',
                        'ajuste_inicial' => 'Ajuste Inicial',
                        'ajuste_conteo' => 'Ajuste Conteo',
                        'ajuste_positivo' => 'Ajuste (+)',
                        'ajuste_negativo' => 'Ajuste (-)',
                        'facturacion_remision' => 'Facturación Remisión',
                        'anulacion_venta_remision' => 'Anulación Vta/Rem',
                        'ajuste_costo_promedio' => 'Ajuste CPP',
                        default => $state,
                    }),
                TextColumn::make('cantidad')
                    ->label('Cantidad')
                    ->numeric(3)
                    ->alignEnd(),
                TextColumn::make('costo_unitario')
                    ->label('Costo Unit.')
                    ->currency()
                    ->alignEnd(),
                TextColumn::make('stock_resultante')
                    ->label('Stock Result.')
                    ->numeric(3)
                    ->alignEnd()
                    ->weight('bold'),
                TextColumn::make('documento_tipo')
                    ->label('Doc. Tipo')
                    ->formatStateUsing(fn (?string $state) => $state ? ucfirst($state) : '-'),
                TextColumn::make('observacion')
                    ->label('Observación')
                    ->limit(40)
                    ->tooltip(fn ($record) => $record->observacion),
            ])
            ->paginated([25, 50, 100]);
    }

    public function getExportUrl(): string
    {
        return route('pdf.kardex', $this->getExportParams());
    }

    public function getExcelUrl(): string
    {
        return route('excel.kardex', $this->getExportParams());
    }

    private function getExportParams(): array
    {
        $params = ['producto' => $this->record->id];
        if ($this->bodega_id) {
            $params['bodega'] = $this->bodega_id;
        }
        if ($this->fecha_desde) {
            $params['desde'] = $this->fecha_desde;
        }
        if ($this->fecha_hasta) {
            $params['hasta'] = $this->fecha_hasta;
        }

        return $params;
    }
}
