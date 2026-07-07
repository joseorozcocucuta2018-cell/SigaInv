@extends('pdf.layout')

@php
    $totalVentas = $datos->sum('total_venta');
    $totalCosto = $datos->sum('total_costo');
    $utilidad = $totalVentas - $totalCosto;
    $margen = $totalVentas > 0 ? round(($utilidad / $totalVentas) * 100, 1) : 0;
@endphp

@section('title')
    Rentabilidad
@endsection

@section('styles')
    @page { size: letter portrait; margin: 10mm; }
    table { width: 100%; border-collapse: collapse; }
    thead th { background: #1e40af; color: white; padding: 5px 4px; text-align: left; font-size: 9px; text-transform: uppercase; }
    tbody td { padding: 4px; border-bottom: 1px solid #e5e7eb; font-size: 9px; }
    tbody tr:nth-child(even) { background: #f9fafb; }
    .text-right { text-align: right; }
    .text-center { text-align: center; }
    .bold { font-weight: bold; }
    .positivo { color: #16a34a; }
    .negativo { color: #dc2626; }
    .totales { margin-top: 10px; background: #f0f4ff; padding: 8px 12px; border-radius: 6px; }
    .totales td { border: none; padding: 3px 8px; font-size: 10px; }
@endsection

@section('header-right')
    <div style="font-size:16px; font-weight:bold; color:#1e40af;">RENTABILIDAD</div>
@endsection

@section('content')
    @if($filtros)
        <div style="font-size:9px; color:#666; margin-bottom:8px;">
            Filtros:
            @if($filtros['desde'] ?? null) Desde: {{ $filtros['desde'] }} | @endif
            @if($filtros['hasta'] ?? null) Hasta: {{ $filtros['hasta'] }} | @endif
            @if($filtros['categoria'] ?? null) Categoría: {{ $filtros['categoria'] }} @endif
        </div>
    @endif

    <table>
        <thead>
            <tr>
                <th>Producto</th>
                <th class="text-right">Cantidad Vendida</th>
                <th class="text-right">Total Ventas</th>
                <th class="text-right">Total Costo</th>
                <th class="text-right">Utilidad</th>
                <th class="text-right">Margen</th>
            </tr>
        </thead>
        <tbody>
            @if(count($datos) > 0)
                @foreach ($datos as $item)
                    @php
                        $utilidadItem = $item->total_venta - $item->total_costo;
                        $margenItem = $item->total_venta > 0 ? round(($utilidadItem / $item->total_venta) * 100, 1) : 0;
                    @endphp
                    <tr>
                        <td>{{ $item->producto_nombre ?? '—' }}</td>
                        <td class="text-right">{{ number_format($item->cantidad_vendida, 0, ',', '.') }}</td>
                        <td class="text-right">${{ number_format($item->total_venta, 0, ',', '.') }}</td>
                        <td class="text-right">${{ number_format($item->total_costo, 0, ',', '.') }}</td>
                        <td class="text-right {{ $utilidadItem >= 0 ? 'positivo' : 'negativo' }}">${{ number_format($utilidadItem, 0, ',', '.') }}</td>
                        <td class="text-right {{ $margenItem >= 0 ? 'positivo' : 'negativo' }}">{{ $margenItem }}%</td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="6" class="text-center" style="padding:20px; color:#999;">No hay datos de rentabilidad para los filtros seleccionados</td>
                </tr>
            @endif
        </tbody>
    </table>

    <table class="totales">
        <tr>
            <td class="bold">Total ventas: ${{ number_format($totalVentas, 0, ',', '.') }}</td>
            <td class="text-right">Total costo: ${{ number_format($totalCosto, 0, ',', '.') }}</td>
            <td class="text-right {{ $utilidad >= 0 ? 'positivo' : 'negativo' }} bold">Utilidad: ${{ number_format($utilidad, 0, ',', '.') }}</td>
            <td class="text-right {{ $margen >= 0 ? 'positivo' : 'negativo' }} bold">Margen: {{ $margen }}%</td>
        </tr>
    </table>
@endsection
