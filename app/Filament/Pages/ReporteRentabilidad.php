<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Enums\VentaEstado;
use App\Models\Categoria;
use App\Models\Cliente;
use App\Models\DetalleVenta;
use App\Models\Venta;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
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

class ReporteRentabilidad extends Page implements HasForms, HasTable
{
    use InteractsWithForms, InteractsWithTable;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-chart-bar';

    protected static \UnitEnum|string|null $navigationGroup = 'Reportes';

    protected static ?string $navigationLabel = 'Rentabilidad';

    protected static ?string $title = 'Rentabilidad por Venta';

    protected static ?int $navigationSort = 20;

    protected string $view = 'filament.pages.reporte-rentabilidad';

    public static function canAccess(): bool
    {
        return Auth::user()?->can('reporte.ver') ?? false;
    }

    public ?string $vista = 'ventas';

    public ?int $cliente_id = null;

    public ?int $categoria_id = null;

    public ?string $fecha_desde = null;

    public ?string $fecha_hasta = null;

    protected function getForms(): array
    {
        return ['filtersForm'];
    }

    public function filtersForm(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Select::make('vista')
                    ->label('Agrupar por')
                    ->options([
                        'ventas' => 'Por venta',
                        'productos' => 'Por producto',
                    ])
                    ->default('ventas')
                    ->live(),
                Select::make('cliente_id')
                    ->label('Cliente')
                    ->options(Cliente::where('id', '!=', 1)->orderBy('nombre')->pluck('nombre', 'id'))
                    ->searchable()
                    ->placeholder('Todos')
                    ->live(),
                Select::make('categoria_id')
                    ->label('Categoría')
                    ->options(Categoria::orderBy('nombre')->pluck('nombre', 'id'))
                    ->searchable()
                    ->placeholder('Todas')
                    ->live(),
                DatePicker::make('fecha_desde')
                    ->label('Desde')
                    ->live(),
                DatePicker::make('fecha_hasta')
                    ->label('Hasta')
                    ->live(),
            ])
            ->columns(5);
    }

    public function table(Table $table): Table
    {
        if (($this->vista ?? 'ventas') === 'productos') {
            return $this->tablaProductos($table);
        }

        return $this->tablaVentas($table);
    }

    protected function tablaVentas(Table $table): Table
    {
        return $table
            ->query(
                Venta::query()
                    ->whereIn('estado', [VentaEstado::CONFIRMADA, VentaEstado::PAGADA])
                    ->with(['cliente', 'detalles'])
                    ->when($this->cliente_id, fn (Builder $q) => $q->where('cliente_id', $this->cliente_id))
                    ->when($this->fecha_desde, fn (Builder $q) => $q->whereDate('fecha', '>=', $this->fecha_desde))
                    ->when($this->fecha_hasta, fn (Builder $q) => $q->whereDate('fecha', '<=', $this->fecha_hasta))
                    ->when($this->categoria_id, fn (Builder $q) => $q->whereHas('detalles.producto', fn ($sq) => $sq->where('categoria_id', $this->categoria_id)))
            )
            ->defaultSort('fecha', 'desc')
            ->columns([
                TextColumn::make('numero')
                    ->label('Factura')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('cliente.nombre')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable()
                    ->limit(30),
                TextColumn::make('fecha')
                    ->label('Fecha')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('total')
                    ->label('Venta')
                    ->currency()
                    ->alignEnd()
                    ->sortable(),
                TextColumn::make('costo_total')
                    ->label('Costo')
                    ->getStateUsing(fn ($record) => $record->detalles->sum(fn ($d) => ($d->costo_unitario ?? 0) * $d->cantidad))
                    ->currency()
                    ->alignEnd(),
                TextColumn::make('utilidad')
                    ->label('Utilidad')
                    ->getStateUsing(function ($record) {
                        $costo = $record->detalles->sum(fn ($d) => ($d->costo_unitario ?? 0) * $d->cantidad);

                        return $record->total - $costo;
                    })
                    ->currency()
                    ->alignEnd()
                    ->weight('bold')
                    ->color(fn ($state) => $state >= 0 ? 'success' : 'danger'),
                TextColumn::make('margen')
                    ->label('% Margen')
                    ->getStateUsing(function ($record) {
                        if (! $record->total || $record->total == 0) {
                            return 0;
                        }
                        $costo = $record->detalles->sum(fn ($d) => ($d->costo_unitario ?? 0) * $d->cantidad);

                        return round((($record->total - $costo) / $record->total) * 100, 1);
                    })
                    ->suffix('%')
                    ->alignEnd()
                    ->color(fn ($state) => $state >= 20 ? 'success' : ($state >= 0 ? 'warning' : 'danger')),
            ])
            ->paginated([25, 50, 100]);
    }

    protected function tablaProductos(Table $table): Table
    {
        return $table
            ->query(
                DetalleVenta::query()
                    ->whereHas('venta', fn (Builder $q) => $q
                        ->whereIn('estado', [VentaEstado::CONFIRMADA, VentaEstado::PAGADA])
                        ->when($this->cliente_id, fn (Builder $sq) => $sq->where('cliente_id', $this->cliente_id))
                        ->when($this->fecha_desde, fn (Builder $sq) => $sq->whereDate('fecha', '>=', $this->fecha_desde))
                        ->when($this->fecha_hasta, fn (Builder $sq) => $sq->whereDate('fecha', '<=', $this->fecha_hasta))
                    )
                    ->when($this->categoria_id, fn (Builder $q) => $q->whereHas('producto', fn ($sq) => $sq->where('categoria_id', $this->categoria_id)))
                    ->with(['producto.categoria'])
                    ->selectRaw('producto_id,
                        SUM(cantidad) as total_cantidad,
                        SUM(precio_unitario * cantidad) as total_ingreso,
                        SUM(COALESCE(descuento_unitario, 0) * cantidad) as total_descuento,
                        SUM(COALESCE(costo_unitario, 0) * cantidad) as total_costo')
                    ->groupBy('producto_id')
            )
            ->defaultSort('total_ingreso', 'desc')
            ->columns([
                TextColumn::make('producto.nombre')
                    ->label('Producto')
                    ->searchable()
                    ->sortable()
                    ->limit(35),
                TextColumn::make('producto.categoria.nombre')
                    ->label('Categoría')
                    ->placeholder('—'),
                TextColumn::make('total_cantidad')
                    ->label('Cant. Vendida')
                    ->numeric(0)
                    ->alignEnd()
                    ->sortable(),
                TextColumn::make('total_ingreso')
                    ->label('Ingreso Bruto')
                    ->currency()
                    ->alignEnd()
                    ->sortable(),
                TextColumn::make('total_costo')
                    ->label('Costo Total')
                    ->currency()
                    ->alignEnd()
                    ->sortable(),
                TextColumn::make('utilidad_producto')
                    ->label('Utilidad')
                    ->getStateUsing(fn ($record) => ($record->total_ingreso - $record->total_descuento) - $record->total_costo)
                    ->currency()
                    ->alignEnd()
                    ->weight('bold')
                    ->color(fn ($state) => $state >= 0 ? 'success' : 'danger'),
                TextColumn::make('margen_producto')
                    ->label('% Margen')
                    ->getStateUsing(function ($record) {
                        $neto = $record->total_ingreso - $record->total_descuento;
                        if ($neto <= 0) {
                            return 0;
                        }

                        return round((($neto - $record->total_costo) / $neto) * 100, 1);
                    })
                    ->suffix('%')
                    ->alignEnd()
                    ->color(fn ($state) => $state >= 20 ? 'success' : ($state >= 0 ? 'warning' : 'danger')),
            ])
            ->paginated([25, 50, 100]);
    }

    public function getTotales(): array
    {
        $ventas = Venta::query()
            ->whereIn('estado', [VentaEstado::CONFIRMADA, VentaEstado::PAGADA])
            ->with('detalles')
            ->when($this->cliente_id, fn (Builder $q) => $q->where('cliente_id', $this->cliente_id))
            ->when($this->fecha_desde, fn (Builder $q) => $q->whereDate('fecha', '>=', $this->fecha_desde))
            ->when($this->fecha_hasta, fn (Builder $q) => $q->whereDate('fecha', '<=', $this->fecha_hasta))
            ->when($this->categoria_id, fn (Builder $q) => $q->whereHas('detalles.producto', fn ($sq) => $sq->where('categoria_id', $this->categoria_id)))
            ->get();

        $totalVentas = $ventas->sum('total');
        $totalCosto = $ventas->sum(fn ($v) => $v->detalles->sum(fn ($d) => ($d->costo_unitario ?? 0) * $d->cantidad));
        $utilidad = $totalVentas - $totalCosto;
        $margen = $totalVentas > 0 ? round(($utilidad / $totalVentas) * 100, 1) : 0;

        return [
            'total_ventas' => $totalVentas,
            'total_costo' => $totalCosto,
            'utilidad' => $utilidad,
            'margen' => $margen,
            'cantidad' => $ventas->count(),
        ];
    }

    public function getExportUrl(): string
    {
        return route('pdf.rentabilidad', $this->getExportParams());
    }

    public function getExcelUrl(): string
    {
        return route('excel.rentabilidad', $this->getExportParams());
    }

    private function getExportParams(): array
    {
        $params = [];
        if ($this->vista) {
            $params['vista'] = $this->vista;
        }
        if ($this->cliente_id) {
            $params['cliente'] = $this->cliente_id;
        }
        if ($this->categoria_id) {
            $params['categoria'] = $this->categoria_id;
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
