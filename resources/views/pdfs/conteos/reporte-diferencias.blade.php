@extends('pdf.layout')

@php
    $diferencias = $conteo->detalles->filter(fn ($d) => (float) $d->diferencia !== 0.0);
    $positivas = $diferencias->where('diferencia', '>', 0);
    $negativas = $diferencias->where('diferencia', '<', 0);
    $totalPositivas = $positivas->sum(fn ($d) => $d->producto?->precio_costo ?? 0) ?: null;
@endphp

@section('title')
    Reporte de Diferencias: {{ $conteo->numero }}
@endsection

@section('header-right')
    <div style="font-size:14px; font-weight:bold; color:#1e3a5f;">Reporte de Diferencias</div>
    <div style="font-size:11px; color:#666;">N° {{ $conteo->numero }}</div>
    <div style="font-size:9px; color:#999; margin-top:3px;">
        Bodega: {{ $conteo->bodega->nombre ?? '—' }} ·
        Estado: {{ ucfirst($conteo->estado) }}
    </div>
@endsection

@section('content')
    <div style="margin-bottom:12px; display:table; width:100%; font-size:9px;">
        <div style="display:table-cell; width:33%; padding:6px 8px; background:#fef3c7; border-radius:4px;">
            <strong>Sobrantes:</strong> {{ $positivas->count() }}
        </div>
        <div style="display:table-cell; width:2%;">&nbsp;</div>
        <div style="display:table-cell; width:33%; padding:6px 8px; background:#fee2e2; border-radius:4px;">
            <strong>Faltantes:</strong> {{ $negativas->count() }}
        </div>
        <div style="display:table-cell; width:2%;">&nbsp;</div>
        <div style="display:table-cell; width:30%; padding:6px 8px; background:#dbeafe; border-radius:4px;">
            <strong>Total Diferencias:</strong> {{ $diferencias->count() }}
        </div>
    </div>

    @if ($diferencias->isNotEmpty())
    <table style="width:100%; border-collapse:collapse;">
        <thead>
            <tr>
                <th style="background:#1e3a5f; color:white; padding:6px 8px; text-align:left; font-size:9px; width:8%;">#</th>
                <th style="background:#1e3a5f; color:white; padding:6px 8px; text-align:left; font-size:9px; width:12%;">Código</th>
                <th style="background:#1e3a5f; color:white; padding:6px 8px; text-align:left; font-size:9px;">Producto</th>
                <th style="background:#1e3a5f; color:white; padding:6px 8px; text-align:right; font-size:9px; width:12%;">Stock Sist.</th>
                <th style="background:#1e3a5f; color:white; padding:6px 8px; text-align:right; font-size:9px; width:12%;">Contado</th>
                <th style="background:#1e3a5f; color:white; padding:6px 8px; text-align:right; font-size:9px; width:12%;">Diferencia</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($diferencias as $i => $detalle)
                @php
                    $diff = (float) $detalle->diferencia;
                    $colorFondo = $diff > 0 ? '#dcfce7' : '#fee2e2';
                    $colorTexto = $diff > 0 ? '#166534' : '#991b1b';
                @endphp
                <tr>
                    <td style="padding:5px 8px; border-bottom:1px solid #e2e8f0; font-size:9px;">{{ $i + 1 }}</td>
                    <td style="padding:5px 8px; border-bottom:1px solid #e2e8f0; font-size:9px;">{{ $detalle->producto->codigo ?? '—' }}</td>
                    <td style="padding:5px 8px; border-bottom:1px solid #e2e8f0; font-size:9px;">{{ $detalle->producto->nombre ?? '—' }}</td>
                    <td style="padding:5px 8px; border-bottom:1px solid #e2e8f0; font-size:9px; text-align:right;">{{ number_format($detalle->stock_sistema, 3, ',', '.') }}</td>
                    <td style="padding:5px 8px; border-bottom:1px solid #e2e8f0; font-size:9px; text-align:right;">{{ $detalle->cantidad_contada !== null ? number_format($detalle->cantidad_contada, 3, ',', '.') : '—' }}</td>
                    <td style="padding:5px 8px; border-bottom:1px solid #e2e8f0; font-size:9px; text-align:right; background:{{ $colorFondo }}; color:{{ $colorTexto }}; font-weight:bold;">
                        {{ $diff > 0 ? '+' : '' }}{{ number_format($diff, 3, ',', '.') }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    @else
        <div style="padding:20px; text-align:center; color:#999; font-size:10px; background:#f0f9ff; border-radius:4px;">
            No se encontraron diferencias. El stock físico coincide con el stock del sistema.
        </div>
    @endif

    @if ($conteo->observacion)
    <div style="margin-top:20px; padding:10px; background:#f8fafc; border-left:3px solid #1e3a5f; font-size:9px;">
        <strong>Observación:</strong> {{ $conteo->observacion }}
    </div>
    @endif
@endsection
