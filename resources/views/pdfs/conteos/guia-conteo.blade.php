@extends('pdf.layout')

@section('title')
    Guía de Conteo: {{ $conteo->numero }}
@endsection

@section('header-right')
    <div style="font-size:14px; font-weight:bold; color:#1e3a5f;">Guía de Conteo Físico</div>
    <div style="font-size:11px; color:#666;">N° {{ $conteo->numero }}</div>
    <div style="font-size:9px; color:#999; margin-top:3px;">
        Bodega: {{ $conteo->bodega->nombre ?? '—' }}
    </div>
@endsection

@section('content')
    <table style="width:100%; border-collapse:collapse; margin-bottom:15px;">
        <thead>
            <tr>
                <th style="background:#1e3a5f; color:white; padding:6px 8px; text-align:left; font-size:9px; width:8%;">#</th>
                <th style="background:#1e3a5f; color:white; padding:6px 8px; text-align:left; font-size:9px; width:15%;">Código</th>
                <th style="background:#1e3a5f; color:white; padding:6px 8px; text-align:left; font-size:9px;">Producto</th>
                <th style="background:#1e3a5f; color:white; padding:6px 8px; text-align:right; font-size:9px; width:15%;">Stock Sistema</th>
                <th style="background:#1e3a5f; color:white; padding:6px 8px; text-align:right; font-size:9px; width:15%;">Cant. Contada</th>
                <th style="background:#1e3a5f; color:white; padding:6px 8px; text-align:center; font-size:9px; width:12%;">Lote</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($conteo->detalles as $i => $detalle)
                <tr>
                    <td style="padding:5px 8px; border-bottom:1px solid #e2e8f0; font-size:9px;">{{ $i + 1 }}</td>
                    <td style="padding:5px 8px; border-bottom:1px solid #e2e8f0; font-size:9px;">{{ $detalle->producto->codigo ?? '—' }}</td>
                    <td style="padding:5px 8px; border-bottom:1px solid #e2e8f0; font-size:9px;">{{ $detalle->producto->nombre ?? '—' }}</td>
                    <td style="padding:5px 8px; border-bottom:1px solid #e2e8f0; font-size:9px; text-align:right;">{{ number_format($detalle->stock_sistema, 3, ',', '.') }}</td>
                    <td style="padding:5px 8px; border-bottom:1px solid #e2e8f0; font-size:9px; text-align:right;">&nbsp;</td>
                    <td style="padding:5px 8px; border-bottom:1px solid #e2e8f0; font-size:9px; text-align:center;">{{ $detalle->lote ?? '' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="padding:20px; text-align:center; color:#999;">Sin productos registrados para este conteo.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top:20px; font-size:8px; color:#666;">
        <strong>Instrucciones:</strong> Imprima este documento y distribúyalo al equipo de conteo. Anote la cantidad física contada en la columna "Cant. Contada". Al finalizar, registre las cantidades en el sistema.
    </div>

    <div style="margin-top:30px; display:table; width:100%;">
        <div style="display:table-cell; width:50%; text-align:center; border-top:1px solid #333; padding-top:5px; font-size:9px;">
            <strong>Responsable del Conteo</strong>
        </div>
        <div style="display:table-cell; width:50%; text-align:center; border-top:1px solid #333; padding-top:5px; font-size:9px;">
            <strong>Supervisor</strong>
        </div>
    </div>
@endsection
