<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Enums\VentaEstado;
use App\Models\Cliente;
use App\Models\User;
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

class ReporteVentas extends Page implements HasForms, HasTable
{
    use InteractsWithForms, InteractsWithTable;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-shopping-cart';

    protected static \UnitEnum|string|null $navigationGroup = 'Reportes';

    protected static ?string $navigationLabel = 'Ventas';

    protected static ?string $title = 'Reporte de Ventas';

    protected static ?int $navigationSort = 30;

    protected string $view = 'filament.pages.reporte-ventas';

    public static function canAccess(): bool
    {
        return Auth::user()?->can('reporte.ver') ?? false;
    }

    public ?int $cliente_id = null;

    public ?string $vendedor = null;

    public ?string $estado = null;

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
                DatePicker::make('fecha_desde')
                    ->label('Desde')
                    ->live(),
                DatePicker::make('fecha_hasta')
                    ->label('Hasta')
                    ->live(),
                Select::make('cliente_id')
                    ->label('Cliente')
                    ->options(Cliente::where('id', '!=', 1)->orderBy('nombre')->pluck('nombre', 'id'))
                    ->searchable()
                    ->placeholder('Todos')
                    ->live(),
                Select::make('vendedor')
                    ->label('Vendedor')
                    ->options(
                        User::role(['vendedor', 'administrador'])->orderBy('name')->pluck('name', 'id')
                    )
                    ->searchable()
                    ->placeholder('Todos')
                    ->live(),
                Select::make('estado')
                    ->label('Estado')
                    ->options(VentaEstado::class)
                    ->placeholder('Todos')
                    ->live(),
            ])
            ->columns(5);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Venta::query()
                    ->with(['cliente', 'usuario'])
                    ->when($this->cliente_id, fn (Builder $q) => $q->where('cliente_id', $this->cliente_id))
                    ->when($this->vendedor, fn (Builder $q) => $q->where('usuario_id', $this->vendedor))
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
                TextColumn::make('cliente.nombre')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable()
                    ->limit(30),
                TextColumn::make('fecha')
                    ->label('Fecha')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('usuario.name')
                    ->label('Vendedor'),
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
        $query = Venta::query()
            ->when($this->cliente_id, fn (Builder $q) => $q->where('cliente_id', $this->cliente_id))
            ->when($this->vendedor, fn (Builder $q) => $q->where('usuario_id', $this->vendedor))
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
        return route('pdf.reporte-ventas', $this->getExportParams());
    }

    public function getExcelUrl(): string
    {
        return route('excel.reporte-ventas', $this->getExportParams());
    }

    private function getExportParams(): array
    {
        $params = [];
        if ($this->cliente_id) {
            $params['cliente'] = $this->cliente_id;
        }
        if ($this->vendedor) {
            $params['vendedor'] = $this->vendedor;
        }
        if ($this->estado) {
            $params['estado'] = $this->estado;
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
