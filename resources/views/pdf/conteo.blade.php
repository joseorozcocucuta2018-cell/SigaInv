@extends('pdf.layout')

@section('title')
    Conteo Físico: {{ $conteo->numero }}
@endsection

@section('styles')
    @page { margin: 10mm; size: letter portrait; }
    .divider { border: none; border-top: 2px solid #1e3a5f; margin: 10px 0; }
    table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
    thead th { background: #1e3a5f; color: white; padding: 6px 8px; text-align: left; font-size: 9px; }
    tbody td { padding: 5px 8px; border-bottom: 1px solid #e2e8f0; font-size: 9px; }
    .num { text-align: right; }
@endsection

@section('header-right')
    <div style="font-size:16px; font-weight:bold; color:#1e3a5f;">Conteo Físico: {{ $conteo->numero }}</div>
@endsection

@section('content')
    <table>
        <thead>
            <tr>
                <th>Producto</th>
                <th class="num">Sistema</th>
                <th class="num">Contado</th>
                <th class="num">Diferencia</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($conteo->detalles as $detalle)
            <tr>
                <td>{{ $detalle->producto->nombre }}</td>
                <td class="num">{{ number_format($detalle->stock_sistema, 2) }}</td>
                <td class="num">{{ $detalle->cantidad_contada !== null ? number_format($detalle->cantidad_contada, 2) : '—' }}</td>
                <td class="num">{{ $detalle->cantidad_contada !== null ? number_format($detalle->diferencia, 2) : '—' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
@endsection
