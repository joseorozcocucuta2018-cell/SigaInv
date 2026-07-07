@extends('pdf.layout')

@php
    $totalSubtotal = $ventas->sum('subtotal');
    $totalImpuesto = $ventas->sum('impuestos');
    $totalValor = $ventas->sum('total');
@endphp

@section('title')
    Reporte de Ventas
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
    .totales { margin-top: 10px; background: #f0f4ff; padding: 8px 12px; border-radius: 6px; }
    .totales td { border: none; padding: 3px 8px; font-size: 10px; }
@endsection

@section('header-right')
    <div style="font-size:16px; font-weight:bold; color:#1e40af;">REPORTE DE VENTAS</div>
@endsection

@section('content')
    @if($filtros)
        <div style="font-size:9px; color:#666; margin-bottom:8px;">
            Filtros:
            @if($filtros['cliente'] ?? null) Cliente: {{ $filtros['cliente'] }} | @endif
            @if($filtros['vendedor'] ?? null) Vendedor: {{ $filtros['vendedor'] }} | @endif
            @if($filtros['desde'] ?? null) Desde: {{ $filtros['desde'] }} | @endif
            @if($filtros['hasta'] ?? null) Hasta: {{ $filtros['hasta'] }} @endif
        </div>
    @endif

    <table>
        <thead>
            <tr>
                <th>Factura</th>
                <th>Fecha</th>
                <th>Cliente</th>
                <th class="text-right">Subtotal</th>
                <th class="text-right">Impuesto</th>
                <th class="text-right">Total</th>
                <th class="text-center">Estado</th>
            </tr>
        </thead>
        <tbody>
            @if(count($ventas) > 0)
                @foreach ($ventas as $venta)
                    @php $estadoLabel = is_object($venta->estado) ? $venta->estado->label() : ucfirst($venta->estado); @endphp
                    <tr>
                        <td>{{ $venta->numero }}</td>
                        <td>{{ $venta->fecha?->format('d/m/Y') ?? '—' }}</td>
                        <td>{{ $venta->cliente?->nombre ?? '—' }}</td>
                        <td class="text-right">${{ number_format($venta->subtotal, 0, ',', '.') }}</td>
                        <td class="text-right">${{ number_format($venta->impuestos, 0, ',', '.') }}</td>
                        <td class="text-right bold">${{ number_format($venta->total, 0, ',', '.') }}</td>
                        <td class="text-center">{{ $estadoLabel }}</td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="7" class="text-center" style="padding:20px; color:#999;">No hay ventas con los filtros seleccionados</td>
                </tr>
            @endif
        </tbody>
    </table>

    <table class="totales">
        <tr>
            <td class="bold">Total facturas: {{ $ventas->count() }}</td>
            <td class="text-right">Subtotal: ${{ number_format($totalSubtotal, 0, ',', '.') }}</td>
            <td class="text-right">Impuesto: ${{ number_format($totalImpuesto, 0, ',', '.') }}</td>
            <td class="text-right bold">Total: ${{ number_format($totalValor, 0, ',', '.') }}</td>
        </tr>
    </table>
@endsection
