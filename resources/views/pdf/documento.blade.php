@extends('pdf.layout')

@php
    $titulos = ['venta' => 'Factura de Venta', 'remision' => 'Remisión', 'cotizacion' => 'Cotización'];
    $titulo  = $titulos[$tipo] ?? 'Documento';

    $paleta = [
        'venta'      => ['h' => '#1e3a5f', 'a' => '#2563eb', 'l' => '#dbeafe'],
        'remision'   => ['h' => '#14532d', 'a' => '#16a34a', 'l' => '#dcfce7'],
        'cotizacion' => ['h' => '#1e3a5f', 'a' => '#2563eb', 'l' => '#dbeafe'],
    ];
    $p = $paleta[$tipo] ?? $paleta['venta'];

    $estadoKey   = isset($documento->estado) ? (is_object($documento->estado) ? $documento->estado->value : $documento->estado) : null;
    $estadoLabel = isset($documento->estado) ? (is_object($documento->estado) ? $documento->estado->label() : ucfirst($documento->estado)) : null;
    $estadoBadge = [
        'borrador'   => 'background:#fef9c3;color:#854d0e;',
        'pendiente'  => 'background:#fef9c3;color:#854d0e;',
        'enviada'    => 'background:#dbeafe;color:#1e40af;',
        'confirmada' => 'background:#dcfce7;color:#166534;',
        'confirmado' => 'background:#dcfce7;color:#166534;',
        'aceptada'   => 'background:#dcfce7;color:#166534;',
        'anulada'    => 'background:#fee2e2;color:#991b1b;',
        'rechazada'  => 'background:#fee2e2;color:#991b1b;',
        'vencida'    => 'background:#f3f4f6;color:#374151;',
        'pagada'     => 'background:#dcfce7;color:#166534;',
    ];
    $badgeStyle = $estadoBadge[$estadoKey] ?? 'background:#f3f4f6;color:#374151;';

    $tieneDescuentos = collect($documento->detalles)->contains(fn($d) => ($d->descuento_unitario ?? 0) > 0);

    $impuestosDesglose = [];
    foreach ($documento->detalles as $d) {
        if ($d->impuesto && $d->impuesto->porcentaje > 0) {
            $label = $d->impuesto->nombre . ' (' . $d->impuesto->porcentaje . '%)';
            $base  = $d->subtotal;
            $monto = $base * ($d->impuesto->porcentaje / 100);
            $impuestosDesglose[$label] = ($impuestosDesglose[$label] ?? 0) + $monto;
        }
    }
@endphp

@section('title')
    {{ $titulo }} — {{ $documento->numero }}
@endsection

@section('styles')
    @page { margin: 10mm; size: letter portrait; }
    .header { display: table; width: 100%; margin-bottom: 15px; }
    .header-left { display: table-cell; width: 50%; vertical-align: top; }
    .header-right { display: table-cell; width: 50%; vertical-align: top; text-align: right; }
    .titulo { font-size: 16px; font-weight: bold; color: {{ $p['h'] }}; }
    .numero { font-size: 12px; color: #666; }
    .badge { display: inline-block; padding: 3px 10px; border-radius: 4px; font-size: 9px; font-weight: bold; margin-top: 5px; {{ $badgeStyle }} }
    .meta-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
    .meta-table td { padding: 3px 5px; font-size: 9px; }
    .meta-label { font-weight: bold; width: 25%; color: #666; }
    .cliente-box { background: #f8fafc; padding: 8px; border-radius: 4px; margin-bottom: 15px; border: 1px solid #e2e8f0; }
    .cliente-box strong { color: {{ $p['h'] }}; }
    .items-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
    .items-table th { background: {{ $p['h'] }}; color: white; padding: 6px 8px; text-align: left; font-size: 9px; font-weight: bold; }
    .items-table td { padding: 5px 8px; border-bottom: 1px solid #e2e8f0; font-size: 9px; }
    .items-table .num { text-align: right; }
    .totals-table { width: 250px; margin-left: auto; border-collapse: collapse; }
    .totals-table td { padding: 3px 8px; font-size: 9px; }
    .totals-table .total-final { font-weight: bold; font-size: 11px; border-top: 2px solid #333; }
    .observaciones { font-size: 9px; color: #555; margin: 15px 0; }
    .firmas { margin-top: 40px; display: table; width: 100%; }
    .firma-box { display: table-cell; width: 45%; text-align: center; border-top: 1px solid #999; padding-top: 5px; font-size: 9px; color: #666; }
    .text-right { text-align: right; }
    .text-center { text-align: center; }
@endsection

@section('header-right')
    <div class="titulo">{{ $titulo }}</div>
    <div class="numero">N° {{ $documento->numero }}</div>
    @if($estadoLabel)
        <div class="badge">{{ strtoupper($estadoLabel) }}</div>
    @endif
@endsection

@section('content')
    <table class="meta-table">
        <tr>
            <td class="meta-label">Fecha:</td>
            <td>{{ $documento->fecha?->format('d/m/Y') ?? now()->format('d/m/Y') }}</td>
            @if($documento->usuario)
                <td class="meta-label">Asesor:</td>
                <td>{{ $documento->usuario->name }}</td>
            @endif
        </tr>
        @if($tipo === 'cotizacion' && ($documento->fecha_vigencia ?? false))
        <tr>
            <td class="meta-label">Vigencia:</td>
            <td>{{ $documento->fecha_vigencia->format('d/m/Y') }}</td>
        </tr>
        @endif
        @if($documento->fecha_vencimiento ?? false)
        <tr>
            <td class="meta-label">Vencimiento:</td>
            <td>{{ $documento->fecha_vencimiento->format('d/m/Y') }}</td>
        </tr>
        @endif
        @if($documento->bodega ?? false)
        <tr>
            <td class="meta-label">Bodega:</td>
            <td>{{ $documento->bodega->nombre }}</td>
        </tr>
        @endif
        @if($tipo === 'venta' && ($documento->cotizacion_id ?? false))
        <tr>
            <td class="meta-label">Cotización:</td>
            <td>{{ $documento->cotizacion?->numero }}</td>
        </tr>
        @endif
        @if($tipo === 'venta' && ($documento->remision_id ?? false))
        <tr>
            <td class="meta-label">Remisión:</td>
            <td>{{ $documento->remision?->numero }}</td>
        </tr>
        @endif
        @if($tipo === 'venta' && ($documento->saldo_pendiente ?? 0) > 0)
        <tr>
            <td class="meta-label">Saldo:</td>
            <td>$ {{ number_format($documento->saldo_pendiente, 0, ',', '.') }}</td>
        </tr>
        @endif
    </table>

    @php $cliente = $documento->cliente; @endphp
    <div class="cliente-box">
        <strong>Cliente:</strong> {{ $cliente?->nombre ?? '—' }}<br>
        <span style="font-size: 9px;">
            {{ $cliente?->tipo_documento ?? 'Doc.' }}: {{ $cliente?->documento ?? '—' }}
            @if($cliente?->direccion1) · Dir: {{ $cliente->direccion1 }} @endif
            @if($cliente?->ciudad) · {{ $cliente->ciudad->nombre }} @endif
            @if($cliente?->telefono) · Tel: {{ $cliente->telefono }} @endif
            @if($cliente?->email) · {{ $cliente->email }} @endif
        </span>
    </div>

    <table class="items-table">
        <thead>
            <tr>
                <th width="5%">#</th>
                <th>Producto / Descripción</th>
                <th width="10%" class="num">Cant.</th>
                <th width="15%" class="num">Precio Unit.</th>
                @if($tieneDescuentos)<th width="12%" class="num">Descuento</th>@endif
                <th width="15%" class="num">Total</th>
            </tr>
        </thead>
        <tbody>
            @if(count($documento->detalles) > 0)
                @foreach ($documento->detalles as $i => $detalle)
                <tr>
                    <td class="num">{{ $i + 1 }}</td>
                    <td>
                        {{ $detalle->producto?->nombre ?? '—' }}
                        @if($detalle->producto?->codigo)<br><span style="color:#666;font-size:8px;">Ref: {{ $detalle->producto->codigo }}</span>@endif
                        @if($detalle->lote ?? false)<br><span style="color:#666;font-size:8px;">Lote: {{ $detalle->lote }}</span>@endif
                    </td>
                    <td class="num">{{ number_format($detalle->cantidad, 2, ',', '.') }}</td>
                    <td class="num">$ {{ number_format($detalle->precio_unitario, 0, ',', '.') }}</td>
                    @if($tieneDescuentos)
                    <td class="num">
                        @if(($detalle->descuento_unitario ?? 0) > 0)
                            - $ {{ number_format($detalle->descuento_unitario * $detalle->cantidad, 0, ',', '.') }}
                        @else
                            —
                        @endif
                    </td>
                    @endif
                    <td class="num"><strong>$ {{ number_format($detalle->subtotal, 0, ',', '.') }}</strong></td>
                </tr>
                @endforeach
            @else
                <tr><td colspan="{{ $tieneDescuentos ? 6 : 5 }}" class="text-center" style="padding: 20px; color: #999;">Sin ítems registrados</td></tr>
            @endif
        </tbody>
    </table>

    <table class="totals-table">
        <tr>
            <td>Subtotal:</td>
            <td class="text-right">$ {{ number_format($documento->subtotal ?? 0, 0, ',', '.') }}</td>
        </tr>
        @if(($documento->descuento ?? 0) > 0)
        <tr>
            <td>Descuento:</td>
            <td class="text-right" style="color: #dc2626;">- $ {{ number_format($documento->descuento, 0, ',', '.') }}</td>
        </tr>
        @endif
        @foreach ($impuestosDesglose as $label => $monto)
        <tr>
            <td>{{ $label }}:</td>
            <td class="text-right">$ {{ number_format($monto, 0, ',', '.') }}</td>
        </tr>
        @endforeach
        @if(empty($impuestosDesglose) && ($documento->impuestos ?? 0) > 0)
        <tr>
            <td>Impuestos:</td>
            <td class="text-right">$ {{ number_format($documento->impuestos, 0, ',', '.') }}</td>
        </tr>
        @endif
        <tr class="total-final">
            <td>TOTAL:</td>
            <td class="text-right">$ {{ number_format($documento->total ?? 0, 0, ',', '.') }}</td>
        </tr>
    </table>

    @if($documento->observaciones || $empresa?->notas_factura || $tipo === 'cotizacion')
    <div class="observaciones">
        @if($documento->observaciones)
            <strong>Observaciones:</strong> {{ $documento->observaciones }}<br>
        @endif
        @if($empresa?->notas_factura)
            <strong>Notas:</strong> {{ $empresa->notas_factura }}<br>
        @endif
        @if($tipo === 'cotizacion')
            <span style="font-size: 8px; color: #666;">
                * Los precios cotizados están sujetos a disponibilidad de inventario.
                @if($documento->fecha_vigencia ?? false)
                    Oferta válida hasta el {{ $documento->fecha_vigencia->format('d/m/Y') }}.
                @endif
            </span>
        @endif
    </div>
    @endif

    <div class="firmas">
        <div class="firma-box">
            {{ $documento->cliente?->nombre ?? '' }}<br>
            <span style="color: #999;">Cliente</span>
        </div>
        <div style="width: 10%;"></div>
        <div class="firma-box">
            {{ $documento->usuario?->name ?? '' }}<br>
            <span style="color: #999;">Asesor</span>
        </div>
    </div>
@endsection
