<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Enums\VentaEstado;
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
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ReporteVentasVendedor extends Page implements HasForms, HasTable
{
    use InteractsWithForms, InteractsWithTable;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-user-group';

    protected static \UnitEnum|string|null $navigationGroup = 'Reportes';

    protected static ?string $navigationLabel = 'Ventas por Vendedor';

    protected static ?string $title = 'Ventas por Vendedor';

    protected static ?int $navigationSort = 35;

    protected string $view = 'filament.pages.reporte-ventas-vendedor';

    public static function canAccess(): bool
    {
        return Auth::user()?->can('reporte.ver') ?? false;
    }

    public ?string $fecha_desde = null;

    public ?string $fecha_hasta = null;

    public ?int $vendedor_id = null;

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
                Select::make('vendedor_id')
                    ->label('Vendedor')
                    ->options(
                        User::role(['administrador', 'auxiliar', 'vendedor'])
                            ->orderBy('name')
                            ->pluck('name', 'id')
                    )
                    ->searchable()
                    ->placeholder('Todos')
                    ->live(),
            ])
            ->columns(3);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Venta::query()
                    ->join('users', 'ventas.usuario_id', '=', 'users.id')
                    ->whereIn('ventas.estado', [VentaEstado::CONFIRMADA, VentaEstado::PAGADA])
                    ->when($this->fecha_desde, fn (Builder $q) => $q->whereDate('ventas.fecha', '>=', $this->fecha_desde))
                    ->when($this->fecha_hasta, fn (Builder $q) => $q->whereDate('ventas.fecha', '<=', $this->fecha_hasta))
                    ->when($this->vendedor_id, fn (Builder $q) => $q->where('ventas.usuario_id', $this->vendedor_id))
                    ->selectRaw('ventas.usuario_id, users.name as vendedor_nombre, COUNT(*) as total_facturas, SUM(ventas.subtotal) as total_subtotal, SUM(ventas.impuestos) as total_impuesto, SUM(ventas.total) as total_ventas')
                    ->groupBy('ventas.usuario_id', 'users.name')
                    ->orderByDesc('total_ventas')
            )
            ->columns([
                TextColumn::make('vendedor_nombre')
                    ->label('Vendedor'),
                TextColumn::make('total_facturas')
                    ->label('# Facturas')
                    ->numeric(0)
                    ->alignEnd(),
                TextColumn::make('total_subtotal')
                    ->label('Subtotal')
                    ->currency()
                    ->alignEnd(),
                TextColumn::make('total_impuesto')
                    ->label('Impuesto')
                    ->currency()
                    ->alignEnd(),
                TextColumn::make('total_ventas')
                    ->label('Total Ventas')
                    ->currency()
                    ->alignEnd()
                    ->weight('bold'),
                TextColumn::make('ticket_promedio')
                    ->label('Ticket Promedio')
                    ->getStateUsing(fn ($record) => $record->total_facturas > 0 ? $record->total_ventas / $record->total_facturas : 0)
                    ->currency()
                    ->alignEnd(),
            ])
            ->paginated([25, 50, 100])
            ->defaultSort(null);
    }

    protected function paginateTableQuery(Builder $query): CursorPaginator|Paginator
    {
        return $query->paginate($this->getTableRecordsPerPage());
    }

    public function getTotales(): array
    {
        $query = Venta::query()
            ->whereIn('estado', [VentaEstado::CONFIRMADA, VentaEstado::PAGADA])
            ->when($this->fecha_desde, fn (Builder $q) => $q->whereDate('fecha', '>=', $this->fecha_desde))
            ->when($this->fecha_hasta, fn (Builder $q) => $q->whereDate('fecha', '<=', $this->fecha_hasta))
            ->when($this->vendedor_id, fn (Builder $q) => $q->where('usuario_id', $this->vendedor_id));

        return [
            'total_vendedores' => (clone $query)->distinct('usuario_id')->count('usuario_id'),
            'total_facturas' => $query->count(),
            'total_valor' => $query->sum('total'),
        ];
    }

    public function getExportUrl(): string
    {
        return route('pdf.ventas-vendedor', $this->getExportParams());
    }

    public function getExcelUrl(): string
    {
        return route('excel.ventas-vendedor', $this->getExportParams());
    }

    private function getExportParams(): array
    {
        $params = [];
        if ($this->vendedor_id) {
            $params['vendedor'] = $this->vendedor_id;
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
