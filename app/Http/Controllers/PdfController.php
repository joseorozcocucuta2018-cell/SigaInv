<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\CompraEstado;
use App\Enums\CotizacionEstado;
use App\Enums\VentaEstado;
use App\Models\Bodega;
use App\Models\Categoria;
use App\Models\Cliente;
use App\Models\Compra;
use App\Models\ConteoFisico;
use App\Models\Cotizacion;
use App\Models\Empresa;
use App\Models\MovimientoInventario;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\Remision;
use App\Models\StockBodega;
use App\Models\User;
use App\Models\Venta;
use App\Services\CotizacionService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PdfController extends Controller
{
    /**
     * Generar vista imprimible común para venta, remisión o cotización
     */
    private function generarPDF($documento, string $tipo, array $extraRelations = [])
    {
        $relations = ['cliente.ciudad', 'bodega', 'detalles.producto', 'detalles.impuesto', 'usuario'];
        if (! empty($extraRelations)) {
            $relations = array_merge($relations, $extraRelations);
        }
        $documento->load($relations);

        // Marcar cotización como enviada al imprimir/visualizar
        if ($tipo === 'cotizacion' && $documento->estado?->value === CotizacionEstado::PENDIENTE->value) {
            try {
                CotizacionService::cambiarEstado($documento, CotizacionEstado::ENVIADA);
            } catch (\InvalidArgumentException) {
                // Si la transición no es válida, ignoramos
            }
        }

        $titulos = [
            'venta' => 'Factura de Venta',
            'remision' => 'Remisión',
            'cotizacion' => 'Cotización',
        ];

        return view('imprimir.documento', [
            'documento' => $documento,
            'empresa' => Empresa::actual()?->load('ciudad'),
            'tipo' => $tipo,
            'titulo' => $titulos[$tipo] ?? 'Documento',
        ]);
    }

    public function conteo(ConteoFisico $conteo)
    {
        abort_unless(Auth::user()?->can('conteo_fisico.ver'), 403);

        $conteo->load(['detalles.producto']);

        return Pdf::loadView('pdf.conteo', [
            'conteo' => $conteo,
            'empresa' => Empresa::actual()?->load('ciudad'),
        ])
            ->setPaper('letter', 'portrait')
            ->stream("conteo-{$conteo->numero}.pdf");
    }

    public function guiaConteo(ConteoFisico $conteo)
    {
        abort_unless(Auth::user()?->can('conteo_fisico.ver'), 403);

        $conteo->load(['detalles.producto', 'bodega', 'usuario']);

        return app('laravel-mpdf')->loadView('pdfs.conteos.guia-conteo', [
            'conteo' => $conteo,
            'empresa' => Empresa::actual()?->load('ciudad'),
        ], [], [
            'format' => 'letter',
            'orientation' => 'P',
        ])->stream("guia-conteo-{$conteo->numero}.pdf");
    }

    public function diferenciasConteo(ConteoFisico $conteo)
    {
        abort_unless(Auth::user()?->can('conteo_fisico.ver'), 403);

        $conteo->load(['detalles.producto', 'bodega', 'usuario']);

        return app('laravel-mpdf')->loadView('pdfs.conteos.reporte-diferencias', [
            'conteo' => $conteo,
            'empresa' => Empresa::actual()?->load('ciudad'),
        ], [], [
            'format' => 'letter',
            'orientation' => 'P',
        ])->stream("diferencias-conteo-{$conteo->numero}.pdf");
    }

    public function venta(Venta $venta)
    {
        abort_unless($this->puedeVerDocumento($venta, 'venta.ver', 'portal.mis_ventas'), 403);

        return $this->generarPDF($venta, 'venta', ['cotizacion', 'remision']);
    }

    public function remision(Remision $remision)
    {
        abort_unless($this->puedeVerDocumento($remision, 'remision.ver', 'portal.mis_remisiones'), 403);

        return $this->generarPDF($remision, 'remision', ['cotizacion']);
    }

    public function cotizacion(Cotizacion $cotizacion)
    {
        abort_unless($this->puedeVerDocumento($cotizacion, 'cotizacion.ver', 'portal.mis_cotizaciones'), 403);

        return $this->generarPDF($cotizacion, 'cotizacion');
    }

    /**
     * Verifica si el usuario autenticado puede ver el documento.
     * - Staff (admin/auxiliar/contador/vendedor): usa el permiso de admin tradicional.
     * - Cliente del portal (guard 'cliente'): exige que sea SU documento.
     */
    private function puedeVerDocumento(mixed $documento, string $permisoStaff, string $permisoPortal): bool
    {
        $user = Auth::user();

        if ($user instanceof User && $user->can($permisoStaff)) {
            return true;
        }

        $cliente = Auth::guard('cliente')->user();

        if ($cliente instanceof Cliente) {
            return (int) $documento->cliente_id === (int) $cliente->id;
        }

        return false;
    }

    public function kardex(Request $request)
    {
        abort_unless(Auth::user()?->can('producto.ver'), 403);

        $producto = Producto::with(['categoria', 'unidadMedida'])->findOrFail($request->query('producto'));

        $query = MovimientoInventario::with('bodega')
            ->where('producto_id', $producto->id)
            ->orderBy('fecha_movimiento', 'asc');

        $filtros = [];

        if ($request->query('bodega')) {
            $query->where('bodega_id', $request->query('bodega'));
            $filtros['bodega'] = Bodega::find($request->query('bodega'))?->nombre;
        }
        if ($request->query('desde')) {
            $query->whereDate('fecha_movimiento', '>=', $request->query('desde'));
            $filtros['desde'] = Carbon::parse($request->query('desde'))->format('d/m/Y');
        }
        if ($request->query('hasta')) {
            $query->whereDate('fecha_movimiento', '<=', $request->query('hasta'));
            $filtros['hasta'] = Carbon::parse($request->query('hasta'))->format('d/m/Y');
        }

        return Pdf::loadView('pdf.kardex', [
            'producto' => $producto,
            'movimientos' => $query->get(),
            'empresa' => Empresa::actual(),
            'filtros' => $filtros ?: null,
        ])
            ->setPaper('letter', 'landscape')
            ->stream("kardex-{$producto->codigo}.pdf");
    }

    public function cartera(Request $request)
    {
        abort_unless(Auth::user()?->can('reporte.ver'), 403);

        $query = Venta::with('cliente')
            ->whereIn('estado', [VentaEstado::CONFIRMADA, VentaEstado::PAGADA])
            ->where('saldo_pendiente', '>', 0)
            ->orderBy('fecha_vencimiento', 'asc');

        $filtros = [];

        if ($request->query('cliente')) {
            $query->where('cliente_id', $request->query('cliente'));
            $filtros['cliente'] = Cliente::find($request->query('cliente'))?->nombre;
        }
        if ($request->query('desde')) {
            $query->whereDate('fecha_vencimiento', '>=', $request->query('desde'));
            $filtros['desde'] = Carbon::parse($request->query('desde'))->format('d/m/Y');
        }
        if ($request->query('hasta')) {
            $query->whereDate('fecha_vencimiento', '<=', $request->query('hasta'));
            $filtros['hasta'] = Carbon::parse($request->query('hasta'))->format('d/m/Y');
        }
        if ($request->query('estado')) {
            $filtros['estado'] = $request->query('estado');
            match ($request->query('estado')) {
                'vencidas' => $query->whereNotNull('fecha_vencimiento')->whereDate('fecha_vencimiento', '<', now()),
                'por_vencer' => $query->whereNotNull('fecha_vencimiento')->whereDate('fecha_vencimiento', '>=', now()),
                'sin_fecha' => $query->whereNull('fecha_vencimiento'),
                default => null,
            };
        }

        return Pdf::loadView('pdf.cartera', [
            'ventas' => $query->get(),
            'empresa' => Empresa::actual(),
            'filtros' => $filtros ?: null,
        ])
            ->setPaper('letter', 'portrait')
            ->stream('cartera.pdf');
    }

    public function reporteVentas(Request $request)
    {
        abort_unless(Auth::user()?->can('reporte.ver'), 403);
        abort_unless(Auth::user()?->can('venta.ver'), 403);

        $query = Venta::with(['cliente', 'usuario'])
            ->orderBy('fecha', 'desc');

        $filtros = [];

        if ($request->query('cliente')) {
            $query->where('cliente_id', $request->query('cliente'));
            $filtros['cliente'] = Cliente::find($request->query('cliente'))?->nombre;
        }
        if ($request->query('vendedor')) {
            $query->where('usuario_id', $request->query('vendedor'));
            $filtros['vendedor'] = User::find($request->query('vendedor'))?->name;
        }
        if ($request->query('estado')) {
            $query->where('estado', $request->query('estado'));
            $estado = VentaEstado::tryFrom($request->query('estado'));
            $filtros['estado'] = $estado?->label() ?? $request->query('estado');
        }
        if ($request->query('desde')) {
            $query->whereDate('fecha', '>=', $request->query('desde'));
            $filtros['desde'] = Carbon::parse($request->query('desde'))->format('d/m/Y');
        }
        if ($request->query('hasta')) {
            $query->whereDate('fecha', '<=', $request->query('hasta'));
            $filtros['hasta'] = Carbon::parse($request->query('hasta'))->format('d/m/Y');
        }

        return Pdf::loadView('pdf.reporte-ventas', [
            'ventas' => $query->get(),
            'empresa' => Empresa::actual(),
            'filtros' => $filtros ?: null,
        ])
            ->setPaper('letter', 'portrait')
            ->stream('reporte-ventas.pdf');
    }

    public function reporteCompras(Request $request)
    {
        abort_unless(Auth::user()?->can('reporte.ver'), 403);
        abort_unless(Auth::user()?->can('compra.ver'), 403);

        $query = Compra::with(['proveedor', 'usuario'])
            ->orderBy('fecha', 'desc');

        $filtros = [];

        if ($request->query('proveedor')) {
            $query->where('proveedor_id', $request->query('proveedor'));
            $filtros['proveedor'] = Proveedor::find($request->query('proveedor'))?->nombre;
        }
        if ($request->query('estado')) {
            $query->where('estado', $request->query('estado'));
            $estado = CompraEstado::tryFrom($request->query('estado'));
            $filtros['estado'] = $estado?->label() ?? $request->query('estado');
        }
        if ($request->query('desde')) {
            $query->whereDate('fecha', '>=', $request->query('desde'));
            $filtros['desde'] = Carbon::parse($request->query('desde'))->format('d/m/Y');
        }
        if ($request->query('hasta')) {
            $query->whereDate('fecha', '<=', $request->query('hasta'));
            $filtros['hasta'] = Carbon::parse($request->query('hasta'))->format('d/m/Y');
        }

        return Pdf::loadView('pdf.reporte-compras', [
            'compras' => $query->get(),
            'empresa' => Empresa::actual(),
            'filtros' => $filtros ?: null,
        ])
            ->setPaper('letter', 'portrait')
            ->stream('reporte-compras.pdf');
    }

    public function inventarioValorizado(Request $request)
    {
        abort_unless(Auth::user()?->can('reporte.ver'), 403);
        abort_unless(Auth::user()?->can('stock.ver'), 403);

        $query = StockBodega::with(['producto.categoria', 'producto.unidadMedida', 'bodega'])
            ->orderBy('producto_id', 'asc');

        $filtros = [];

        if ($request->query('bodega')) {
            $query->where('bodega_id', $request->query('bodega'));
            $filtros['bodega'] = Bodega::find($request->query('bodega'))?->nombre;
        }
        if ($request->query('categoria')) {
            $query->whereHas('producto', fn ($q) => $q->where('categoria_id', $request->query('categoria')));
            $filtros['categoria'] = Categoria::find($request->query('categoria'))?->nombre;
        }
        if ($request->query('con_stock')) {
            $filtros['con_stock'] = $request->query('con_stock');
            match ($request->query('con_stock')) {
                'con_stock' => $query->where('cantidad', '>', 0),
                'sin_stock' => $query->where('cantidad', '=', 0),
                default => null,
            };
        }

        return Pdf::loadView('pdf.inventario-valorizado', [
            'registros' => $query->get(),
            'empresa' => Empresa::actual(),
            'filtros' => $filtros ?: null,
        ])
            ->setPaper('letter', 'landscape')
            ->stream('inventario-valorizado.pdf');
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

        $filtros = [];

        if ($request->query('vendedor')) {
            $query->where('ventas.usuario_id', $request->query('vendedor'));
            $filtros['vendedor'] = User::find($request->query('vendedor'))?->name;
        }
        if ($request->query('desde')) {
            $query->whereDate('ventas.fecha', '>=', $request->query('desde'));
            $filtros['desde'] = Carbon::parse($request->query('desde'))->format('d/m/Y');
        }
        if ($request->query('hasta')) {
            $query->whereDate('ventas.fecha', '<=', $request->query('hasta'));
            $filtros['hasta'] = Carbon::parse($request->query('hasta'))->format('d/m/Y');
        }

        return Pdf::loadView('pdf.ventas-vendedor', [
            'vendedores' => $query->get(),
            'empresa' => Empresa::actual(),
            'filtros' => $filtros ?: null,
        ])
            ->setPaper('letter', 'portrait')
            ->stream('ventas-vendedor.pdf');
    }

    public function rentabilidad(Request $request)
    {
        abort_unless(Auth::user()?->can('reporte.ver'), 403);

        $vista = $request->query('vista', 'ventas');
        $filtros = [];

        $ventasQuery = Venta::with(['cliente', 'detalles.producto.categoria'])
            ->whereIn('estado', [VentaEstado::CONFIRMADA, VentaEstado::PAGADA]);

        if ($request->query('cliente')) {
            $ventasQuery->where('cliente_id', $request->query('cliente'));
            $filtros['cliente'] = Cliente::find($request->query('cliente'))?->nombre;
        }
        if ($request->query('categoria')) {
            $ventasQuery->whereHas('detalles.producto', fn ($q) => $q->where('categoria_id', $request->query('categoria')));
            $filtros['categoria'] = Categoria::find($request->query('categoria'))?->nombre;
        }
        if ($request->query('desde')) {
            $ventasQuery->whereDate('fecha', '>=', $request->query('desde'));
            $filtros['desde'] = Carbon::parse($request->query('desde'))->format('d/m/Y');
        }
        if ($request->query('hasta')) {
            $ventasQuery->whereDate('fecha', '<=', $request->query('hasta'));
            $filtros['hasta'] = Carbon::parse($request->query('hasta'))->format('d/m/Y');
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
                            'categoria' => $detalle->producto?->categoria?->nombre,
                            'cantidad' => 0,
                            'total_venta' => 0,
                            'total_costo' => 0,
                        ];
                    }
                    $item = $agrupado[$key];
                    $item['cantidad'] += $detalle->cantidad;
                    $item['total_venta'] += ($detalle->precio_unitario - ($detalle->descuento_unitario ?? 0)) * $detalle->cantidad;
                    $item['total_costo'] += ($detalle->costo_unitario ?? 0) * $detalle->cantidad;
                    $agrupado[$key] = $item;
                }
            }
            $datos = $agrupado->sortByDesc('total_venta')->values();
            $vistaLabel = 'Por producto';
        } else {
            $datos = $ventas->map(fn ($v) => [
                'numero' => $v->numero,
                'cliente' => $v->cliente?->nombre ?? '—',
                'fecha' => $v->fecha?->format('d/m/Y') ?? '—',
                'total_venta' => (float) $v->total,
                'total_costo' => $v->detalles->sum(fn ($d) => ($d->costo_unitario ?? 0) * $d->cantidad),
            ]);
            $vistaLabel = 'Por venta';
        }

        return Pdf::loadView('pdf.rentabilidad', [
            'datos' => $datos,
            'vista' => $vista,
            'vistaLabel' => $vistaLabel,
            'empresa' => Empresa::actual(),
            'filtros' => $filtros ?: null,
        ])
            ->setPaper('letter', 'portrait')
            ->stream('rentabilidad.pdf');
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

        $filtros = [];

        if ($request->query('categoria')) {
            $query->where('categoria_id', $request->query('categoria'));
            $filtros['categoria'] = Categoria::find($request->query('categoria'))?->nombre;
        }
        if ($request->query('bodega')) {
            $query->whereHas('stockBodegas', fn ($q) => $q->where('bodega_id', $request->query('bodega')));
            $filtros['bodega'] = Bodega::find($request->query('bodega'))?->nombre;
        }

        return Pdf::loadView('pdf.productos-sin-movimiento', [
            'productos' => $query->get(),
            'empresa' => Empresa::actual(),
            'dias' => $dias,
            'filtros' => $filtros ?: null,
        ])
            ->setPaper('letter', 'landscape')
            ->stream('productos-sin-movimiento.pdf');
    }
}
