<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Enums\BodegaEstado;
use App\Models\Bodega;
use App\Models\Categoria;
use App\Models\Producto;
use Carbon\Carbon;
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

class ReporteProductosSinMovimiento extends Page implements HasForms, HasTable
{
    use InteractsWithForms, InteractsWithTable;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-archive-box-x-mark';

    protected static \UnitEnum|string|null $navigationGroup = 'Reportes';

    protected static ?string $navigationLabel = 'Sin Movimiento';

    protected static ?string $title = 'Productos sin Movimiento';

    protected static ?int $navigationSort = 60;

    protected string $view = 'filament.pages.reporte-productos-sin-movimiento';

    public static function canAccess(): bool
    {
        return Auth::user()?->can('reporte.ver') ?? false;
    }

    public ?string $dias_sin_movimiento = '30';

    public ?int $categoria_id = null;

    public ?int $bodega_id = null;

    protected function getForms(): array
    {
        return ['filtersForm'];
    }

    public function filtersForm(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Select::make('dias_sin_movimiento')
                    ->label('Dias sin movimiento')
                    ->options([
                        '30' => '30 dias',
                        '60' => '60 dias',
                        '90' => '90 dias',
                        '180' => '180 dias',
                    ])
                    ->default('30')
                    ->live(),
                Select::make('categoria_id')
                    ->label('Categoria')
                    ->options(Categoria::orderBy('nombre')->pluck('nombre', 'id'))
                    ->searchable()
                    ->placeholder('Todas')
                    ->live(),
                Select::make('bodega_id')
                    ->label('Bodega')
                    ->options(Bodega::where('estado', BodegaEstado::ACTIVO)->orderBy('nombre')->pluck('nombre', 'id'))
                    ->searchable()
                    ->placeholder('Todas')
                    ->live(),
            ])
            ->columns(3);
    }

    public function table(Table $table): Table
    {
        $dias = (int) ($this->dias_sin_movimiento ?? 30);

        return $table
            ->query(
                Producto::query()
                    ->where('activo', true)
                    ->whereDoesntHave('movimientosInventario', fn (Builder $sq) => $sq->where('fecha_movimiento', '>=', now()->subDays($dias)))
                    ->when($this->categoria_id, fn (Builder $q) => $q->where('categoria_id', $this->categoria_id))
                    ->when($this->bodega_id, fn (Builder $q) => $q->whereHas('stockBodegas', fn (Builder $sq) => $sq->where('bodega_id', $this->bodega_id)))
                    ->with(['categoria', 'unidadMedida', 'stockBodegas.bodega'])
            )
            ->defaultSort('codigo', 'asc')
            ->columns([
                TextColumn::make('codigo')
                    ->label('Codigo')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('nombre')
                    ->label('Producto')
                    ->searchable()
                    ->sortable()
                    ->limit(35),
                TextColumn::make('categoria.nombre')
                    ->label('Categoria')
                    ->placeholder('—'),
                TextColumn::make('stock_total')
                    ->label('Stock')
                    ->getStateUsing(fn ($record) => $record->stockBodegas->sum('cantidad'))
                    ->numeric(0)
                    ->alignEnd(),
                TextColumn::make('costo_promedio')
                    ->label('Costo Prom.')
                    ->currency()
                    ->alignEnd(),
                TextColumn::make('valor_inmovilizado')
                    ->label('Valor Inmovilizado')
                    ->getStateUsing(fn ($record) => $record->stockBodegas->sum('cantidad') * $record->costo_promedio)
                    ->currency()
                    ->alignEnd()
                    ->weight('bold')
                    ->color('danger'),
                TextColumn::make('ultimo_movimiento')
                    ->label('Ultimo Movimiento')
                    ->getStateUsing(function ($record) {
                        $ultimo = $record->movimientosInventario()->orderByDesc('fecha_movimiento')->value('fecha_movimiento');

                        return $ultimo ? Carbon::parse($ultimo)->format('d/m/Y') : 'Nunca';
                    }),
            ])
            ->paginated([25, 50, 100]);
    }

    public function getTotales(): array
    {
        $dias = (int) ($this->dias_sin_movimiento ?? 30);

        $productos = Producto::query()
            ->where('activo', true)
            ->where(function (Builder $q) use ($dias) {
                $q->whereDoesntHave('movimientosInventario', fn (Builder $sq) => $sq->where('fecha_movimiento', '>=', now()->subDays($dias)))
                    ->orWhereDoesntHave('movimientosInventario');
            })
            ->when($this->categoria_id, fn (Builder $q) => $q->where('categoria_id', $this->categoria_id))
            ->when($this->bodega_id, fn (Builder $q) => $q->whereHas('stockBodegas', fn (Builder $sq) => $sq->where('bodega_id', $this->bodega_id)))
            ->with('stockBodegas')
            ->get();

        $totalUnidades = $productos->sum(fn ($p) => $p->stockBodegas->sum('cantidad'));
        $valorTotal = $productos->sum(fn ($p) => $p->stockBodegas->sum('cantidad') * $p->costo_promedio);

        return [
            'total_productos' => $productos->count(),
            'total_unidades_inmovilizadas' => $totalUnidades,
            'valor_total_inmovilizado' => $valorTotal,
        ];
    }

    public function getExportUrl(): string
    {
        return route('pdf.productos-sin-movimiento', $this->getExportParams());
    }

    public function getExcelUrl(): string
    {
        return route('excel.productos-sin-movimiento', $this->getExportParams());
    }

    private function getExportParams(): array
    {
        $params = [];
        if ($this->dias_sin_movimiento) {
            $params['dias'] = $this->dias_sin_movimiento;
        }
        if ($this->categoria_id) {
            $params['categoria'] = $this->categoria_id;
        }
        if ($this->bodega_id) {
            $params['bodega'] = $this->bodega_id;
        }

        return $params;
    }
}
