@extends('pdf.layout')

@php
    $totalUnidades = $productos->sum(fn($p) => $p->stockBodegas->sum('cantidad'));
    $valorTotal = $productos->sum(fn($p) => $p->stockBodegas->sum('cantidad') * $p->costo_promedio);
@endphp

@section('title')
    Productos sin Movimiento
@endsection

@section('styles')
    @page { size: letter landscape; margin: 10mm; }
    table { width: 100%; border-collapse: collapse; }
    thead th { background: #1e40af; color: white; padding: 5px 4px; text-align: left; font-size: 9px; text-transform: uppercase; }
    tbody td { padding: 4px; border-bottom: 1px solid #e5e7eb; font-size: 9px; }
    tbody tr:nth-child(even) { background: #f9fafb; }
    .text-right { text-align: right; }
    .text-center { text-align: center; }
    .bold { font-weight: bold; }
    .mora { color: #dc2626; font-weight: bold; }
    .totales { margin-top: 10px; background: #f0f4ff; padding: 8px 12px; border-radius: 6px; }
    .totales td { border: none; padding: 3px 8px; font-size: 10px; }
@endsection

@section('header-right')
    <div style="font-size:16px; font-weight:bold; color:#1e40af;">PRODUCTOS SIN MOVIMIENTO</div>
    <div style="font-size:10px; color:#333;">Dias sin movimiento: {{ $dias }}</div>
@endsection

@section('content')
    @if($filtros)
        <div style="font-size:9px; color:#666; margin-bottom:8px;">
            Filtros:
            @if($filtros['categoria'] ?? null) Categoria: {{ $filtros['categoria'] }} | @endif
            @if($filtros['bodega'] ?? null) Bodega: {{ $filtros['bodega'] }} @endif
        </div>
    @endif

    <table>
        <thead>
            <tr>
                <th>Codigo</th>
                <th>Producto</th>
                <th>Categoria</th>
                <th class="text-right">Stock</th>
                <th>Unidad</th>
                <th class="text-right">Costo Prom.</th>
                <th class="text-right">Valor Inmovilizado</th>
                <th class="text-center">Ultimo Movimiento</th>
            </tr>
        </thead>
        <tbody>
            @if(count($productos) > 0)
                @foreach ($productos as $producto)
                    @php
                        $stock = $producto->stockBodegas->sum('cantidad');
                        $valorInmovilizado = $stock * $producto->costo_promedio;
                        $ultimoMov = $producto->movimientosInventario->sortByDesc('fecha_movimiento')->first();
                        $ultimoLabel = $ultimoMov ? \Carbon\Carbon::parse($ultimoMov->fecha_movimiento)->format('d/m/Y') : 'Nunca';
                    @endphp
                    <tr>
                        <td>{{ $producto->codigo }}</td>
                        <td>{{ $producto->nombre }}</td>
                        <td>{{ $producto->categoria?->nombre ?? '—' }}</td>
                        <td class="text-right">{{ number_format($stock, 0, ',', '.') }}</td>
                        <td>{{ $producto->unidadMedida?->abreviatura ?? '—' }}</td>
                        <td class="text-right">${{ number_format($producto->costo_promedio, 0, ',', '.') }}</td>
                        <td class="text-right bold mora">${{ number_format($valorInmovilizado, 0, ',', '.') }}</td>
                        <td class="text-center">{{ $ultimoLabel }}</td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="8" class="text-center" style="padding:20px; color:#999;">No hay productos sin movimiento en el periodo seleccionado</td>
                </tr>
            @endif
        </tbody>
    </table>

    <table class="totales">
        <tr>
            <td class="bold">Total productos: {{ $productos->count() }}</td>
            <td class="text-right bold">Unidades inmovilizadas: {{ number_format($totalUnidades, 0, ',', '.') }}</td>
            <td class="text-right bold mora">Valor inmovilizado: ${{ number_format($valorTotal, 0, ',', '.') }}</td>
        </tr>
    </table>
@endsection
