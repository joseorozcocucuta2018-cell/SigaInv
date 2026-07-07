@extends('pdf.layout')

@php
    $totalValor = $ventas->sum('total');
    $totalSaldo = $ventas->sum('saldo_pendiente');
@endphp

@section('title')
    Cartera — Cuentas por Cobrar
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
    .mora { color: #dc2626; font-weight: bold; }
    .ok { color: #16a34a; }
    .warn { color: #d97706; }
    .totales { margin-top: 10px; background: #f0f4ff; padding: 8px 12px; border-radius: 6px; }
    .totales td { border: none; padding: 3px 8px; font-size: 10px; }
@endsection

@section('header-right')
    <div style="font-size:16px; font-weight:bold; color:#1e40af;">CUENTAS POR COBRAR</div>
    <div style="font-size:10px; color:#333;">Cuentas por Cobrar</div>
@endsection

@section('content')
    @if($filtros)
        <div style="font-size:9px; color:#666; margin-bottom:8px;">
            Filtros:
            @if($filtros['estado'] ?? null) Estado: {{ $filtros['estado'] }} | @endif
            @if($filtros['cliente'] ?? null) Cliente: {{ $filtros['cliente'] }} | @endif
            @if($filtros['desde'] ?? null) Desde: {{ $filtros['desde'] }} | @endif
            @if($filtros['hasta'] ?? null) Hasta: {{ $filtros['hasta'] }} @endif
        </div>
    @endif

    <table>
        <thead>
            <tr>
                <th>Cliente</th>
                <th>Factura</th>
                <th>Emisión</th>
                <th>Vencimiento</th>
                <th class="text-right">Total</th>
                <th class="text-right">Saldo</th>
                <th class="text-center">Días</th>
            </tr>
        </thead>
        <tbody>
            @if(count($ventas) > 0)
                @foreach ($ventas as $venta)
                    @php
                        $diasLabel = '—';
                        $diasClass = '';
                        if ($venta->fecha_vencimiento) {
                            $dias = (int) now()->startOfDay()->diffInDays($venta->fecha_vencimiento->startOfDay(), false);
                            if ($dias < 0) {
                                $diasLabel = abs($dias) . 'd mora';
                                $diasClass = 'mora';
                            } elseif ($dias === 0) {
                                $diasLabel = 'Hoy';
                                $diasClass = 'warn';
                            } else {
                                $diasLabel = $dias . 'd';
                                $diasClass = 'ok';
                            }
                        }
                    @endphp
                    <tr>
                        <td>{{ $venta->cliente?->nombre ?? '—' }}</td>
                        <td>{{ $venta->numero }}</td>
                        <td>{{ $venta->fecha?->format('d/m/Y') ?? '—' }}</td>
                        <td>{{ $venta->fecha_vencimiento?->format('d/m/Y') ?? 'Sin fecha' }}</td>
                        <td class="text-right">${{ number_format($venta->total, 0, ',', '.') }}</td>
                        <td class="text-right bold mora">${{ number_format($venta->saldo_pendiente, 0, ',', '.') }}</td>
                        <td class="text-center {{ $diasClass }}">{{ $diasLabel }}</td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="7" class="text-center" style="padding:20px; color:#999;">No hay facturas con saldo pendiente</td>
                </tr>
            @endif
        </tbody>
    </table>

    <table class="totales">
        <tr>
            <td class="bold">Total facturas: {{ $ventas->count() }}</td>
            <td class="text-right bold">Total facturado: ${{ number_format($totalValor, 0, ',', '.') }}</td>
            <td class="text-right bold mora">Total por cobrar: ${{ number_format($totalSaldo, 0, ',', '.') }}</td>
        </tr>
    </table>
@endsection
