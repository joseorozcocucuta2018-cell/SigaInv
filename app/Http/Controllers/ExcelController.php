<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\VentaEstado;
use App\Models\Compra;
use App\Models\MovimientoInventario;
use App\Models\Producto;
use App\Models\StockBodega;
use App\Models\Venta;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\XLSX\Writer;

class ExcelController extends Controller
{
    public function kardex(Request $request)
    {
        abort_unless(Auth::user()?->can('producto.ver'), 403);

        $producto = Producto::findOrFail($request->query('producto'));

        $query = MovimientoInventario::with('bodega')
            ->where('producto_id', $producto->id)
            ->orderBy('fecha_movimiento', 'asc');

        if ($request->query('bodega')) {
            $query->where('bodega_id', $request->query('bodega'));
        }
        if ($request->query('desde')) {
            $query->whereDate('fecha_movimiento', '>=', $request->query('desde'));
        }
        if ($request->query('hasta')) {
            $query->whereDate('fecha_movimiento', '<=', $request->query('hasta'));
        }

        $movimientos = $query->get();

        return $this->generarExcel(
            "kardex-{$producto->codigo}.xlsx",
            ['Fecha', 'Bodega', 'Tipo Movimiento', 'Cantidad', 'Costo Unit.', 'Stock Result.', 'Doc. Tipo', 'Observación'],
            $movimientos->map(fn ($m) => [
                $m->fecha_movimiento?->format('d/m/Y H:i'),
                $m->bodega?->nombre ?? '-',
                $m->tipo_movimiento,
                $m->cantidad,
                $m->costo_unitario,
                $m->stock_resultante,
                $m->documento_tipo ? ucfirst($m->documento_tipo) : '-',
                $m->observacion ?? '',
            ])->toArray()
        );
    }

    public function cartera(Request $request)
    {
        abort_unless(Auth::user()?->can('reporte.ver'), 403);

        $query = Venta::with('cliente')
            ->whereIn('estado', [VentaEstado::CONFIRMADA, VentaEstado::PAGADA])
            ->where('saldo_pendiente', '>', 0)
            ->orderBy('fecha_vencimiento', 'asc');

        if ($request->query('cliente')) {
            $query->where('cliente_id', $request->query('cliente'));
        }
        if ($request->query('desde')) {
            $query->whereDate('fecha_vencimiento', '>=', $request->query('desde'));
        }
        if ($request->query('hasta')) {
            $query->whereDate('fecha_vencimiento', '<=', $request->query('hasta'));
        }
        if ($request->query('estado')) {
            match ($request->query('estado')) {
                'vencidas' => $query->whereNotNull('fecha_vencimiento')->whereDate('fecha_vencimiento', '<', now()),
                'por_vencer' => $query->whereNotNull('fecha_vencimiento')->whereDate('fecha_vencimiento', '>=', now()),
                'sin_fecha' => $query->whereNull('fecha_vencimiento'),
                default => null,
            };
        }

        $ventas = $query->get();

        return $this->generarExcel(
            'cartera.xlsx',
            ['Cliente', 'Factura', 'Emisión', 'Vencimiento', 'Total', 'Saldo Pendiente', 'Días'],
            $ventas->map(function ($v) {
                $dias = '—';
                if ($v->fecha_vencimiento) {
                    $d = (int) now()->startOfDay()->diffInDays($v->fecha_vencimiento->startOfDay(), false);
                    $dias = $d < 0 ? abs($d).'d mora' : ($d === 0 ? 'Hoy' : $d.'d');
                }

                return [
                    $v->cliente?->nombre ?? '—',
                    $v->numero,
                    $v->fecha?->format('d/m/Y') ?? '—',
                    $v->fecha_vencimiento?->format('d/m/Y') ?? 'Sin fecha',
                    $v->total,
                    $v->saldo_pendiente,
                    $dias,
                ];
            })->toArray()
        );
    }

    public function reporteVentas(Request $request)
    {
        abort_unless(Auth::user()?->can('reporte.ver'), 403);
        abort_unless(Auth::user()?->can('venta.ver'), 403);

        $query = Venta::with(['cliente', 'usuario'])
            ->orderBy('fecha', 'desc');

        if ($request->query('cliente')) {
            $query->where('cliente_id', $request->query('cliente'));
        }
        if ($request->query('vendedor')) {
            $query->where('usuario_id', $request->query('vendedor'));
        }
        if ($request->query('estado')) {
            $query->where('estado', $request->query('estado'));
        }
        if ($request->query('desde')) {
            $query->whereDate('fecha', '>=', $request->query('desde'));
        }
        if ($request->query('hasta')) {
            $query->whereDate('fecha', '<=', $request->query('hasta'));
        }

        $ventas = $query->get();

        return $this->generarExcel(
            'reporte-ventas.xlsx',
            ['Factura', 'Fecha', 'Cliente', 'Vendedor', 'Subtotal', 'Impuesto', 'Total', 'Estado'],
            $ventas->map(fn ($v) => [
                $v->numero,
                $v->fecha?->format('d/m/Y') ?? '—',
                $v->cliente?->nombre ?? '—',
                $v->usuario?->name ?? '—',
                $v->subtotal,
                $v->impuestos,
                $v->total,
                $v->estado->label(),
            ])->toArray()
        );
    }

    public function reporteCompras(Request $request)
    {
        abort_unless(Auth::user()?->can('reporte.ver'), 403);
        abort_unless(Auth::user()?->can('compra.ver'), 403);

        $query = Compra::with(['proveedor', 'usuario'])
            ->orderBy('fecha', 'desc');

        if ($request->query('proveedor')) {
            $query->where('proveedor_id', $request->query('proveedor'));
        }
        if ($request->query('estado')) {
            $query->where('estado', $request->query('estado'));
        }
        if ($request->query('desde')) {
            $query->whereDate('fecha', '>=', $request->query('desde'));
        }
        if ($request->query('hasta')) {
            $query->whereDate('fecha', '<=', $request->query('hasta'));
        }

        $compras = $query->get();

        return $this->generarExcel(
            'reporte-compras.xlsx',
            ['Factura', 'Fecha', 'Proveedor', 'Subtotal', 'Impuesto', 'Total', 'Estado'],
            $compras->map(fn ($c) => [
                $c->numero,
                $c->fecha?->format('d/m/Y') ?? '—',
                $c->proveedor?->nombre ?? '—',
                $c->subtotal,
                $c->impuestos,
                $c->total,
                $c->estado->label(),
            ])->toArray()
        );
    }

    public function inventarioValorizado(Request $request)
    {
        abort_unless(Auth::user()?->can('reporte.ver'), 403);
        abort_unless(Auth::user()?->can('stock.ver'), 403);

        $query = StockBodega::with(['producto.categoria', 'producto.unidadMedida', 'bodega'])
            ->orderBy('producto_id', 'asc');

        if ($request->query('bodega')) {
            $query->where('bodega_id', $request->query('bodega'));
        }
        if ($request->query('categoria')) {
            $query->whereHas('producto', fn ($q) => $q->where('categoria_id', $request->query('categoria')));
        }
        if ($request->query('con_stock')) {
            match ($request->query('con_stock')) {
                'con_stock' => $query->where('cantidad', '>', 0),
                'sin_stock' => $query->where('cantidad', '=', 0),
                default => null,
            };
        }

        $registros = $query->get();

        return $this->generarExcel(
            'inventario-valorizado.xlsx',
            ['Código', 'Producto', 'Categoría', 'Bodega', 'Stock', 'Unidad', 'Costo Prom.', 'Valor Total'],
            $registros->map(fn ($r) => [
                $r->producto?->codigo ?? '—',
                $r->producto?->nombre ?? '—',
                $r->producto?->categoria?->nombre ?? '—',
                $r->bodega?->nombre ?? '—',
                $r->cantidad,
                $r->producto?->unidadMedida?->abreviatura ?? '—',
                $r->producto?->costo_promedio ?? 0,
                $r->cantidad * ($r->producto?->costo_promedio ?? 0),
            ])->toArray()
        );
    }

    public function ventasVendedor(Request $request)
    {
        abort_unless(Auth::user()?->can('reporte.ver'), 403);
        abort_unless(Auth::user()?->can('venta.ver'), 403);

        $query = Venta::query()
            ->join('users', 'ventas.usuario_id', '=', 'users.id')
            ->whereIn('ventas.estado', [VentaEstado::CONFIRMADA, VentaEstado::PAGADA])
            ->selectRaw('ventas.usuario_id, users.name as vendedor_nombre, COUNT(*) as total_facturas, SUM(ventas.subtotal) as total_subtotal, SUM(ventas.impuestos) as total_impuesto, SUM(ventas.total) as total_ventas')
            ->groupBy('ventas.usuario_id', 'users.name')
            ->orderByDesc('total_ventas');

        if ($request->query('vendedor')) {
            $query->where('usuario_id', $request->query('vendedor'));
        }
        if ($request->query('desde')) {
            $query->whereDate('fecha', '>=', $request->query('desde'));
        }
        if ($request->query('hasta')) {
            $query->whereDate('fecha', '<=', $request->query('hasta'));
        }

        $vendedores = $query->get();

        return $this->generarExcel(
            'ventas-vendedor.xlsx',
            ['Vendedor', '# Facturas', 'Subtotal', 'Impuesto', 'Total Ventas', 'Ticket Promedio'],
            $vendedores->map(fn ($v) => [
                $v->vendedor_nombre ?? '—',
                $v->total_facturas,
                $v->total_subtotal,
                $v->total_impuesto,
                $v->total_ventas,
                $v->total_facturas > 0 ? round($v->total_ventas / $v->total_facturas) : 0,
            ])->toArray()
        );
    }

    public function rentabilidad(Request $request)
    {
        abort_unless(Auth::user()?->can('reporte.ver'), 403);

        $vista = $request->query('vista', 'ventas');

        $ventasQuery = Venta::with(['cliente', 'detalles.producto.categoria'])
            ->whereIn('estado', [VentaEstado::CONFIRMADA, VentaEstado::PAGADA]);

        if ($request->query('cliente')) {
            $ventasQuery->where('cliente_id', $request->query('cliente'));
        }
        if ($request->query('categoria')) {
            $ventasQuery->whereHas('detalles.producto', fn ($q) => $q->where('categoria_id', $request->query('categoria')));
        }
        if ($request->query('desde')) {
            $ventasQuery->whereDate('fecha', '>=', $request->query('desde'));
        }
        if ($request->query('hasta')) {
            $ventasQuery->whereDate('fecha', '<=', $request->query('hasta'));
        }

        $ventas = $ventasQuery->get();

        if ($vista === 'productos') {
            $agrupado = collect();
            foreach ($ventas as $venta) {
                foreach ($venta->detalles as $detalle) {
                    $key = $detalle->producto_id;
                    if (! $agrupado->has($key)) {
                        $agrupado[$key] = [
                            'nombre' => $detalle->producto?->nombre ?? 'N/A',
                            'categoria' => $detalle->producto?->categoria?->nombre ?? '—',
                            'cantidad' => 0,
                            'ingreso' => 0,
                            'costo' => 0,
                        ];
                    }
                    $item = $agrupado[$key];
                    $item['cantidad'] += $detalle->cantidad;
                    $item['ingreso'] += ($detalle->precio_unitario - ($detalle->descuento_unitario ?? 0)) * $detalle->cantidad;
                    $item['costo'] += ($detalle->costo_unitario ?? 0) * $detalle->cantidad;
                    $agrupado[$key] = $item;
                }
            }

            return $this->generarExcel(
                'rentabilidad-productos.xlsx',
                ['Producto', 'Categoría', 'Cantidad', 'Ingreso', 'Costo', 'Utilidad', '% Margen'],
                $agrupado->sortByDesc('ingreso')->map(function ($f) {
                    $ut = $f['ingreso'] - $f['costo'];
                    $mg = $f['ingreso'] > 0 ? round(($ut / $f['ingreso']) * 100, 1) : 0;

                    return [$f['nombre'], $f['categoria'], $f['cantidad'], $f['ingreso'], $f['costo'], $ut, $mg.'%'];
                })->values()->toArray()
            );
        }

        return $this->generarExcel(
            'rentabilidad-ventas.xlsx',
            ['Factura', 'Cliente', 'Fecha', 'Ingreso', 'Costo', 'Utilidad', '% Margen'],
            $ventas->map(function ($v) {
                $costo = $v->detalles->sum(fn ($d) => ($d->costo_unitario ?? 0) * $d->cantidad);
                $ut = $v->total - $costo;
                $mg = $v->total > 0 ? round(($ut / $v->total) * 100, 1) : 0;

                return [$v->numero, $v->cliente?->nombre ?? '—', $v->fecha?->format('d/m/Y'), $v->total, $costo, $ut, $mg.'%'];
            })->toArray()
        );
    }

    public function productosSinMovimiento(Request $request)
    {
        abort_unless(Auth::user()?->can('reporte.ver'), 403);
        abort_unless(Auth::user()?->can('producto.ver'), 403);

        $dias = (int) ($request->query('dias', 30));

        $query = Producto::query()
            ->where('activo', true)
            ->whereDoesntHave('movimientosInventario', fn ($sq) => $sq->where('fecha_movimiento', '>=', now()->subDays($dias)))
            ->with(['categoria', 'unidadMedida', 'stockBodegas', 'movimientosInventario'])
            ->orderBy('codigo', 'asc');

        if ($request->query('categoria')) {
            $query->where('categoria_id', $request->query('categoria'));
        }
        if ($request->query('bodega')) {
            $query->whereHas('stockBodegas', fn ($q) => $q->where('bodega_id', $request->query('bodega')));
        }

        $productos = $query->get();

        return $this->generarExcel(
            'productos-sin-movimiento.xlsx',
            ['Codigo', 'Producto', 'Categoria', 'Stock', 'Unidad', 'Costo Prom.', 'Valor Inmovilizado', 'Ultimo Movimiento'],
            $productos->map(function ($p) {
                $stock = $p->stockBodegas->sum('cantidad');
                $ultimo = $p->movimientosInventario->sortByDesc('fecha_movimiento')->first();

                return [
                    $p->codigo,
                    $p->nombre,
                    $p->categoria?->nombre ?? '—',
                    $stock,
                    $p->unidadMedida?->abreviatura ?? '—',
                    $p->costo_promedio,
                    $stock * $p->costo_promedio,
                    $ultimo ? Carbon::parse($ultimo->fecha_movimiento)->format('d/m/Y') : 'Nunca',
                ];
            })->toArray()
        );
    }

    private function generarExcel(string $filename, array $headers, array $rows)
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'xlsx_').'.xlsx';

        $writer = new Writer;
        $writer->openToFile($tempFile);

        // Header row
        $headerStyle = (new Style)->setFontBold();
        $writer->addRow(Row::fromValues($headers, $headerStyle));

        // Data rows
        foreach ($rows as $row) {
            $writer->addRow(Row::fromValues($row));
        }

        $writer->close();

        return response()->download($tempFile, $filename)->deleteFileAfterSend(true);
    }
}
