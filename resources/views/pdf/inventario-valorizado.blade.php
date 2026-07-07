@extends('pdf.layout')

@php
    $totalUnidades = $registros->sum('cantidad');
    $valorTotal = $registros->sum(fn($r) => $r->cantidad * $r->producto->costo_promedio);
@endphp

@section('title')
    Inventario Valorizado
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
    <div style="font-size:16px; font-weight:bold; color:#1e40af;">INVENTARIO VALORIZADO</div>
@endsection

@section('content')
    @if($filtros)
        <div style="font-size:9px; color:#666; margin-bottom:8px;">
            Filtros:
            @if($filtros['bodega'] ?? null) Bodega: {{ $filtros['bodega'] }} | @endif
            @if($filtros['categoria'] ?? null) Categoría: {{ $filtros['categoria'] }} | @endif
            @if($filtros['con_stock'] ?? null) Stock: {{ $filtros['con_stock'] }} @endif
        </div>
    @endif

    <table>
        <thead>
            <tr>
                <th>Código</th>
                <th>Producto</th>
                <th>Categoría</th>
                <th>Bodega</th>
                <th class="text-right">Stock</th>
                <th>Unidad</th>
                <th class="text-right">Costo Prom.</th>
                <th class="text-right">Valor Total</th>
            </tr>
        </thead>
        <tbody>
            @if(count($registros) > 0)
                @foreach ($registros as $registro)
                    @php $valorItem = $registro->cantidad * $registro->producto->costo_promedio; @endphp
                    <tr>
                        <td>{{ $registro->producto?->codigo ?? '—' }}</td>
                        <td>{{ $registro->producto?->nombre ?? '—' }}</td>
                        <td>{{ $registro->producto?->categoria?->nombre ?? '—' }}</td>
                        <td>{{ $registro->bodega?->nombre ?? '—' }}</td>
                        <td class="text-right">{{ number_format($registro->cantidad, 0, ',', '.') }}</td>
                        <td>{{ $registro->producto?->unidadMedida?->abreviatura ?? '—' }}</td>
                        <td class="text-right">${{ number_format($registro->producto?->costo_promedio ?? 0, 0, ',', '.') }}</td>
                        <td class="text-right bold">${{ number_format($valorItem, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="8" class="text-center" style="padding:20px; color:#999;">No hay registros de inventario</td>
                </tr>
            @endif
        </tbody>
    </table>

    <table class="totales">
        <tr>
            <td class="bold">Total productos: {{ $registros->count() }}</td>
            <td class="text-right bold">Total unidades: {{ number_format($totalUnidades, 0, ',', '.') }}</td>
            <td class="text-right bold mora">Valor del inventario: ${{ number_format($valorTotal, 0, ',', '.') }}</td>
        </tr>
    </table>
@endsection
