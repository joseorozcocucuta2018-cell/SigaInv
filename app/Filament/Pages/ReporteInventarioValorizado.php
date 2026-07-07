<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Enums\BodegaEstado;
use App\Models\Bodega;
use App\Models\Categoria;
use App\Models\StockBodega;
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

class ReporteInventarioValorizado extends Page implements HasForms, HasTable
{
    use InteractsWithForms, InteractsWithTable;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-cube';

    protected static \UnitEnum|string|null $navigationGroup = 'Reportes';

    protected static ?string $navigationLabel = 'Inventario Valorizado';

    protected static ?string $title = 'Inventario Valorizado';

    protected static ?int $navigationSort = 50;

    protected string $view = 'filament.pages.reporte-inventario-valorizado';

    public static function canAccess(): bool
    {
        return Auth::user()?->can('reporte.ver') ?? false;
    }

    public ?int $bodega_id = null;

    public ?int $categoria_id = null;

    public ?string $con_stock = 'con_stock';

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
                    ->options(Bodega::where('estado', BodegaEstado::ACTIVO)->orderBy('nombre')->pluck('nombre', 'id'))
                    ->placeholder('Todas')
                    ->live(),
                Select::make('categoria_id')
                    ->label('Categoría')
                    ->options(Categoria::orderBy('nombre')->pluck('nombre', 'id'))
                    ->placeholder('Todas')
                    ->live(),
                Select::make('con_stock')
                    ->label('Filtro stock')
                    ->options([
                        'todos' => 'Todos',
                        'con_stock' => 'Con stock',
                        'sin_stock' => 'Sin stock',
                    ])
                    ->default('con_stock')
                    ->live(),
            ])
            ->columns(3);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                StockBodega::query()
                    ->with(['producto.categoria', 'producto.unidadMedida', 'bodega'])
                    ->when($this->bodega_id, fn (Builder $q) => $q->where('bodega_id', $this->bodega_id))
                    ->when($this->categoria_id, fn (Builder $q) => $q->whereHas('producto', fn (Builder $p) => $p->where('categoria_id', $this->categoria_id)))
                    ->when($this->con_stock === 'con_stock', fn (Builder $q) => $q->where('cantidad', '>', 0))
                    ->when($this->con_stock === 'sin_stock', fn (Builder $q) => $q->where('cantidad', '=', 0))
            )
            ->defaultSort('producto.nombre', 'asc')
            ->columns([
                TextColumn::make('producto.codigo')
                    ->label('Código')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('producto.nombre')
                    ->label('Producto')
                    ->searchable()
                    ->sortable()
                    ->limit(35),
                TextColumn::make('producto.categoria.nombre')
                    ->label('Categoría')
                    ->placeholder('—'),
                TextColumn::make('bodega.nombre')
                    ->label('Bodega')
                    ->sortable(),
                TextColumn::make('cantidad')
                    ->label('Stock')
                    ->numeric(0)
                    ->alignEnd()
                    ->sortable()
                    ->color(fn ($record) => $record->cantidad <= ($record->producto?->stock_minimo ?? 0) ? 'danger' : null),
                TextColumn::make('producto.unidadMedida.abreviatura')
                    ->label('Und'),
                TextColumn::make('producto.costo_promedio')
                    ->label('Costo Prom.')
                    ->currency()
                    ->alignEnd(),
                TextColumn::make('valor_total')
                    ->label('Valor Total')
                    ->getStateUsing(fn ($record) => $record->cantidad * $record->producto->costo_promedio)
                    ->currency()
                    ->alignEnd()
                    ->weight('bold'),
            ])
            ->paginated([25, 50, 100]);
    }

    public function getTotales(): array
    {
        $query = StockBodega::query()
            ->with('producto')
            ->when($this->bodega_id, fn (Builder $q) => $q->where('bodega_id', $this->bodega_id))
            ->when($this->categoria_id, fn (Builder $q) => $q->whereHas('producto', fn (Builder $p) => $p->where('categoria_id', $this->categoria_id)))
            ->when($this->con_stock === 'con_stock', fn (Builder $q) => $q->where('cantidad', '>', 0))
            ->when($this->con_stock === 'sin_stock', fn (Builder $q) => $q->where('cantidad', '=', 0));

        $registros = $query->get();

        return [
            'total_items' => $registros->count(),
            'total_unidades' => $registros->sum('cantidad'),
            'valor_total_inventario' => $registros->sum(fn ($r) => $r->cantidad * $r->producto->costo_promedio),
        ];
    }

    public function getExportUrl(): string
    {
        return route('pdf.inventario-valorizado', $this->getExportParams());
    }

    public function getExcelUrl(): string
    {
        return route('excel.inventario-valorizado', $this->getExportParams());
    }

    private function getExportParams(): array
    {
        $params = [];
        if ($this->bodega_id) {
            $params['bodega'] = $this->bodega_id;
        }
        if ($this->categoria_id) {
            $params['categoria'] = $this->categoria_id;
        }
        if ($this->con_stock) {
            $params['con_stock'] = $this->con_stock;
        }

        return $params;
    }
}
