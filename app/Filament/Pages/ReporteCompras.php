<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Enums\CompraEstado;
use App\Models\Compra;
use App\Models\Proveedor;
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

class ReporteCompras extends Page implements HasForms, HasTable
{
    use InteractsWithForms, InteractsWithTable;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-truck';

    protected static \UnitEnum|string|null $navigationGroup = 'Reportes';

    protected static ?string $navigationLabel = 'Compras';

    protected static ?string $title = 'Reporte de Compras';

    protected static ?int $navigationSort = 40;

    protected string $view = 'filament.pages.reporte-compras';

    public static function canAccess(): bool
    {
        return Auth::user()?->can('reporte.ver') ?? false;
    }

    public ?string $estado = null;

    public ?int $proveedor_id = null;

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
                Select::make('proveedor_id')
                    ->label('Proveedor')
                    ->options(Proveedor::where('id', '!=', 1)->orderBy('nombre')->pluck('nombre', 'id'))
                    ->searchable()
                    ->placeholder('Todos')
                    ->live(),
                Select::make('estado')
                    ->label('Estado')
                    ->options(CompraEstado::class)
                    ->placeholder('Todos')
                    ->live(),
                DatePicker::make('fecha_desde')
                    ->label('Fecha desde')
                    ->live(),
                DatePicker::make('fecha_hasta')
                    ->label('Fecha hasta')
                    ->live(),
            ])
            ->columns(4);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Compra::query()
                    ->with(['proveedor', 'usuario'])
                    ->when($this->proveedor_id, fn (Builder $q) => $q->where('proveedor_id', $this->proveedor_id))
                    ->when($this->estado, fn (Builder $q) => $q->where('estado', $this->estado))
                    ->when($this->fecha_desde, fn (Builder $q) => $q->whereDate('fecha', '>=', $this->fecha_desde))
                    ->when($this->fecha_hasta, fn (Builder $q) => $q->whereDate('fecha', '<=', $this->fecha_hasta))
            )
            ->defaultSort('fecha', 'desc')
            ->columns([
                TextColumn::make('numero')
                    ->label('Factura')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('proveedor.nombre')
                    ->label('Proveedor')
                    ->searchable()
                    ->sortable()
                    ->limit(30),
                TextColumn::make('fecha')
                    ->label('Fecha')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('subtotal')
                    ->label('Subtotal')
                    ->currency()
                    ->alignEnd(),
                TextColumn::make('impuestos')
                    ->label('Impuesto')
                    ->currency()
                    ->alignEnd(),
                TextColumn::make('total')
                    ->label('Total')
                    ->currency()
                    ->alignEnd()
                    ->weight('bold'),
                TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state->label())
                    ->color(fn ($state) => $state->color()),
            ])
            ->paginated([25, 50, 100]);
    }

    public function getTotales(): array
    {
        $query = Compra::query()
            ->when($this->proveedor_id, fn (Builder $q) => $q->where('proveedor_id', $this->proveedor_id))
            ->when($this->estado, fn (Builder $q) => $q->where('estado', $this->estado))
            ->when($this->fecha_desde, fn (Builder $q) => $q->whereDate('fecha', '>=', $this->fecha_desde))
            ->when($this->fecha_hasta, fn (Builder $q) => $q->whereDate('fecha', '<=', $this->fecha_hasta));

        return [
            'total_facturas' => $query->count(),
            'total_subtotal' => $query->sum('subtotal'),
            'total_impuesto' => $query->sum('impuestos'),
            'total_valor' => $query->sum('total'),
        ];
    }

    public function getExportUrl(): string
    {
        return route('pdf.reporte-compras', $this->getExportParams());
    }

    public function getExcelUrl(): string
    {
        return route('excel.reporte-compras', $this->getExportParams());
    }

    private function getExportParams(): array
    {
        $params = [];
        if ($this->estado) {
            $params['estado'] = $this->estado;
        }
        if ($this->proveedor_id) {
            $params['proveedor'] = $this->proveedor_id;
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
