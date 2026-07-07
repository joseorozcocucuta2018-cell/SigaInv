@extends('pdf.layout')

@php
    $totalFacturas = $vendedores->sum('total_facturas');
    $totalVentas = $vendedores->sum('total_ventas');
@endphp

@section('title')
    Ventas por Vendedor
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
    <div style="font-size:16px; font-weight:bold; color:#1e40af;">VENTAS POR VENDEDOR</div>
    <div style="font-size:10px; color:#333;">Resumen por vendedor</div>
@endsection

@section('content')
    @if($filtros)
        <div style="font-size:9px; color:#666; margin-bottom:8px;">
            Filtros:
            @if($filtros['vendedor'] ?? null) Vendedor: {{ $filtros['vendedor'] }} | @endif
            @if($filtros['desde'] ?? null) Desde: {{ $filtros['desde'] }} | @endif
            @if($filtros['hasta'] ?? null) Hasta: {{ $filtros['hasta'] }} @endif
        </div>
    @endif

    <table>
        <thead>
            <tr>
                <th>Vendedor</th>
                <th class="text-right"># Facturas</th>
                <th class="text-right">Subtotal</th>
                <th class="text-right">Impuesto</th>
                <th class="text-right">Total Ventas</th>
                <th class="text-right">Ticket Promedio</th>
            </tr>
        </thead>
        <tbody>
            @if(count($vendedores) > 0)
                @foreach ($vendedores as $vendedor)
                    @php $ticketPromedio = $vendedor->total_facturas > 0 ? $vendedor->total_ventas / $vendedor->total_facturas : 0; @endphp
                    <tr>
                        <td>{{ $vendedor->vendedor_nombre ?? '—' }}</td>
                        <td class="text-right">{{ number_format($vendedor->total_facturas, 0, ',', '.') }}</td>
                        <td class="text-right">${{ number_format($vendedor->total_subtotal, 0, ',', '.') }}</td>
                        <td class="text-right">${{ number_format($vendedor->total_impuesto, 0, ',', '.') }}</td>
                        <td class="text-right bold">${{ number_format($vendedor->total_ventas, 0, ',', '.') }}</td>
                        <td class="text-right">${{ number_format($ticketPromedio, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="6" class="text-center" style="padding:20px; color:#999;">No hay ventas registradas para los filtros seleccionados</td>
                </tr>
            @endif
        </tbody>
    </table>

    <table class="totales">
        <tr>
            <td class="bold">Total vendedores: {{ $vendedores->count() }}</td>
            <td class="text-right bold">Total facturas: {{ number_format($totalFacturas, 0, ',', '.') }}</td>
            <td class="text-right bold">Total ventas: ${{ number_format($totalVentas, 0, ',', '.') }}</td>
        </tr>
    </table>
@endsection
