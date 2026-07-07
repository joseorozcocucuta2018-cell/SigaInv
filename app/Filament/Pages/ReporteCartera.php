<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Enums\VentaEstado;
use App\Models\Cliente;
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

class ReporteCartera extends Page implements HasForms, HasTable
{
    use InteractsWithForms, InteractsWithTable;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-banknotes';

    protected static \UnitEnum|string|null $navigationGroup = 'Reportes';

    protected static ?string $navigationLabel = 'Cartera';

    protected static ?string $title = 'Cuentas por Cobrar';

    protected static ?int $navigationSort = 10;

    protected string $view = 'filament.pages.reporte-cartera';

    public static function canAccess(): bool
    {
        return Auth::user()?->can('reporte.ver') ?? false;
    }

    public ?string $filtro_estado = null;

    public ?int $cliente_id = null;

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
                Select::make('filtro_estado')
                    ->label('Estado')
                    ->options([
                        'todas' => 'Todas con saldo',
                        'vencidas' => 'Vencidas',
                        'por_vencer' => 'Por vencer',
                        'sin_fecha' => 'Sin fecha vencimiento',
                    ])
                    ->placeholder('Todas con saldo')
                    ->live(),
                Select::make('cliente_id')
                    ->label('Cliente')
                    ->options(Cliente::where('id', '!=', 1)->orderBy('nombre')->pluck('nombre', 'id'))
                    ->searchable()
                    ->placeholder('Todos')
                    ->live(),
                DatePicker::make('fecha_desde')
                    ->label('Vencimiento desde')
                    ->live(),
                DatePicker::make('fecha_hasta')
                    ->label('Vencimiento hasta')
                    ->live(),
            ])
            ->columns(4);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Venta::query()
                    ->whereIn('estado', [VentaEstado::CONFIRMADA, VentaEstado::PAGADA])
                    ->where('saldo_pendiente', '>', 0)
                    ->with(['cliente'])
                    ->when($this->cliente_id, fn (Builder $q) => $q->where('cliente_id', $this->cliente_id))
                    ->when($this->fecha_desde, fn (Builder $q) => $q->whereDate('fecha_vencimiento', '>=', $this->fecha_desde))
                    ->when($this->fecha_hasta, fn (Builder $q) => $q->whereDate('fecha_vencimiento', '<=', $this->fecha_hasta))
                    ->when($this->filtro_estado === 'vencidas', fn (Builder $q) => $q->whereNotNull('fecha_vencimiento')->whereDate('fecha_vencimiento', '<', now()))
                    ->when($this->filtro_estado === 'por_vencer', fn (Builder $q) => $q->whereNotNull('fecha_vencimiento')->whereDate('fecha_vencimiento', '>=', now()))
                    ->when($this->filtro_estado === 'sin_fecha', fn (Builder $q) => $q->whereNull('fecha_vencimiento'))
            )
            ->defaultSort('fecha_vencimiento', 'asc')
            ->columns([
                TextColumn::make('cliente.nombre')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('numero')
                    ->label('Factura')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('fecha')
                    ->label('Fecha Emisión')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('fecha_vencimiento')
                    ->label('Vencimiento')
                    ->date('d/m/Y')
                    ->sortable()
                    ->placeholder('Sin fecha')
                    ->color(fn ($record) => match (true) {
                        $record->fecha_vencimiento === null => 'gray',
                        $record->fecha_vencimiento->isPast() => 'danger',
                        $record->fecha_vencimiento->diffInDays(now()) <= 7 => 'warning',
                        default => 'success',
                    }),
                TextColumn::make('total')
                    ->label('Total')
                    ->currency()
                    ->sortable()
                    ->alignEnd(),
                TextColumn::make('saldo_pendiente')
                    ->label('Saldo')
                    ->currency()
                    ->sortable()
                    ->alignEnd()
                    ->weight('bold')
                    ->color('danger'),
                TextColumn::make('dias_vencida')
                    ->label('Días')
                    ->getStateUsing(function ($record) {
                        if (! $record->fecha_vencimiento) {
                            return '—';
                        }
                        $dias = (int) now()->startOfDay()->diffInDays($record->fecha_vencimiento->startOfDay(), false);
                        if ($dias < 0) {
                            return abs($dias).'d mora';
                        }
                        if ($dias === 0) {
                            return 'Hoy';
                        }

                        return $dias.'d restantes';
                    })
                    ->badge()
                    ->color(fn ($record) => match (true) {
                        $record->fecha_vencimiento === null => 'gray',
                        $record->fecha_vencimiento->isPast() => 'danger',
                        $record->fecha_vencimiento->isToday() => 'warning',
                        default => 'success',
                    }),
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
            ->whereIn('estado', [VentaEstado::CONFIRMADA, VentaEstado::PAGADA])
            ->where('saldo_pendiente', '>', 0)
            ->when($this->cliente_id, fn (Builder $q) => $q->where('cliente_id', $this->cliente_id))
            ->when($this->fecha_desde, fn (Builder $q) => $q->whereDate('fecha_vencimiento', '>=', $this->fecha_desde))
            ->when($this->fecha_hasta, fn (Builder $q) => $q->whereDate('fecha_vencimiento', '<=', $this->fecha_hasta))
            ->when($this->filtro_estado === 'vencidas', fn (Builder $q) => $q->whereNotNull('fecha_vencimiento')->whereDate('fecha_vencimiento', '<', now()))
            ->when($this->filtro_estado === 'por_vencer', fn (Builder $q) => $q->whereNotNull('fecha_vencimiento')->whereDate('fecha_vencimiento', '>=', now()))
            ->when($this->filtro_estado === 'sin_fecha', fn (Builder $q) => $q->whereNull('fecha_vencimiento'));

        return [
            'total_facturas' => $query->count(),
            'total_valor' => $query->sum('total'),
            'total_saldo' => $query->sum('saldo_pendiente'),
        ];
    }

    public function getExportUrl(): string
    {
        return route('pdf.cartera', $this->getExportParams());
    }

    public function getExcelUrl(): string
    {
        return route('excel.cartera', $this->getExportParams());
    }

    private function getExportParams(): array
    {
        $params = [];
        if ($this->filtro_estado) {
            $params['estado'] = $this->filtro_estado;
        }
        if ($this->cliente_id) {
            $params['cliente'] = $this->cliente_id;
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
