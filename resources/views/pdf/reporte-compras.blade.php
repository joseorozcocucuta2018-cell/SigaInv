@extends('pdf.layout')

@php
    $totalSubtotal = $compras->sum('subtotal');
    $totalImpuesto = $compras->sum('impuestos');
    $totalValor = $compras->sum('total');
@endphp

@section('title')
    Reporte de Compras
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
    <div style="font-size:16px; font-weight:bold; color:#1e40af;">REPORTE DE COMPRAS</div>
@endsection

@section('content')
    @if($filtros)
        <div style="font-size:9px; color:#666; margin-bottom:8px;">
            Filtros:
            @if($filtros['proveedor'] ?? null) Proveedor: {{ $filtros['proveedor'] }} | @endif
            @if($filtros['estado'] ?? null) Estado: {{ $filtros['estado'] }} | @endif
            @if($filtros['desde'] ?? null) Desde: {{ $filtros['desde'] }} | @endif
            @if($filtros['hasta'] ?? null) Hasta: {{ $filtros['hasta'] }} @endif
        </div>
    @endif

    <table>
        <thead>
            <tr>
                <th>Factura</th>
                <th>Fecha</th>
                <th>Proveedor</th>
                <th class="text-right">Subtotal</th>
                <th class="text-right">Impuesto</th>
                <th class="text-right">Total</th>
                <th class="text-center">Estado</th>
            </tr>
        </thead>
        <tbody>
            @if(count($compras) > 0)
                @foreach ($compras as $compra)
                    <tr>
                        <td>{{ $compra->numero }}</td>
                        <td>{{ $compra->fecha?->format('d/m/Y') ?? '—' }}</td>
                        <td>{{ $compra->proveedor?->nombre ?? '—' }}</td>
                        <td class="text-right">${{ number_format($compra->subtotal, 0, ',', '.') }}</td>
                        <td class="text-right">${{ number_format($compra->impuestos, 0, ',', '.') }}</td>
                        <td class="text-right bold">${{ number_format($compra->total, 0, ',', '.') }}</td>
                        <td class="text-center">{{ $compra->estado->label() }}</td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="7" class="text-center" style="padding:20px; color:#999;">No hay compras con los filtros seleccionados</td>
                </tr>
            @endif
        </tbody>
    </table>

    <table class="totales">
        <tr>
            <td class="bold">Total facturas: {{ $compras->count() }}</td>
            <td class="text-right">Subtotal: ${{ number_format($totalSubtotal, 0, ',', '.') }}</td>
            <td class="text-right">Impuesto: ${{ number_format($totalImpuesto, 0, ',', '.') }}</td>
            <td class="text-right bold">Total: ${{ number_format($totalValor, 0, ',', '.') }}</td>
        </tr>
    </table>
@endsection
