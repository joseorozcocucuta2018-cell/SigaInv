@extends('pdf.layout')

@php
    $color = '#1e3a5f';
    $colorAccent = '#2563eb';
    $estadoBadge = [
        'borrador'   => 'background:#fef9c3;color:#854d0e;',
        'pendiente'  => 'background:#fef9c3;color:#854d0e;',
        'confirmada' => 'background:#dcfce7;color:#166534;',
        'aceptada'   => 'background:#dcfce7;color:#166534;',
        'anulada'    => 'background:#fee2e2;color:#991b1b;',
        'rechazada'  => 'background:#fee2e2;color:#991b1b;',
        'vencida'    => 'background:#f3f4f6;color:#374151;',
        'pagada'     => 'background:#dcfce7;color:#166534;',
        'pagado'     => 'background:#dcfce7;color:#166534;',
        'parcial'    => 'background:#dbeafe;color:#1e40af;',
    ];
    $totalGeneral  = $registros->sum('total');
    $esVentas      = $tipo === 'ventas';
    $esCotizacion  = $tipo === 'cotizaciones';
@endphp

@section('title')
    {{ $titulo }}
@endsection

@section('styles')
    @page { margin: 10mm; size: letter portrait; }
    .header { display: table; width: 100%; margin-bottom: 15px; }
    .header-left { display: table-cell; width: 60%; vertical-align: top; }
    .header-right { display: table-cell; width: 40%; vertical-align: top; text-align: right; }
    .titulo { font-size: 16px; font-weight: bold; color: {{ $color }}; }
    .subtitulo { font-size: 9px; color: #888; margin-top: 3px; }
    .divider { border: none; border-top: 2px solid {{ $color }}; margin: 10px 0; }
    table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
    thead th { background: {{ $color }}; color: white; padding: 6px 8px; text-align: left; font-size: 9px; font-weight: bold; }
    tbody td { padding: 5px 8px; border-bottom: 1px solid #e2e8f0; font-size: 9px; }
    tbody tr:last-child td { border-bottom: none; }
    .num { text-align: right; }
    .badge { display: inline-block; padding: 2px 7px; border-radius: 3px; font-size: 8px; font-weight: bold; }
    .resumen { width: 220px; margin-left: auto; border-collapse: collapse; }
    .resumen td { padding: 3px 8px; font-size: 9px; }
    .resumen .grand-total { font-weight: bold; font-size: 11px; border-top: 2px solid {{ $color }}; }
@endsection

@section('header-right')
    <div style="font-size:16px; font-weight:bold; color:{{ $color }};">{{ $titulo }}</div>
    <div style="font-size:9px; color:#888; margin-top:3px;">
        {{ $registros->count() }} {{ $registros->count() === 1 ? 'registro' : 'registros' }}
        &nbsp;·&nbsp; Generado: {{ now()->format('d/m/Y H:i') }}
    </div>
@endsection

@section('content')
    <table>
        <thead>
            <tr>
                <th width="12%">Nro.</th>
                <th>{{ $esVentas ? 'Cliente' : 'Cliente' }}</th>
                <th width="11%" class="num">Fecha</th>
                @if($esVentas)
                    <th width="11%" class="num">Vencimiento</th>
                    <th width="14%" class="num">Total</th>
                    <th width="14%" class="num">Saldo</th>
                    <th width="10%" class="num">Estado</th>
                @else
                    <th width="11%" class="num">Vigencia</th>
                    <th width="14%" class="num">Total</th>
                    <th width="10%" class="num">Estado</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @if(count($registros) > 0)
                @foreach ($registros as $doc)
                @php
                    $estadoRaw = is_object($doc->estado) ? $doc->estado->value : $doc->estado;
                    if ($esVentas) {
                        $estadoRaw   = $doc->estado_pago ?? $estadoRaw;
                        $estadoLabel = match($estadoRaw) {
                            'pendiente' => 'Pendiente',
                            'parcial'   => 'Parcial',
                            'pagado'    => 'Pagado',
                            'anulada'   => 'Anulada',
                            default     => ucfirst($estadoRaw),
                        };
                    } else {
                        $estadoLabel = ucfirst($estadoRaw);
                    }
                    $bs = $estadoBadge[$estadoRaw] ?? 'background:#f3f4f6;color:#374151;';
                @endphp
                <tr>
                    <td>{{ $doc->numero }}</td>
                    <td>{{ $doc->cliente?->nombre ?? '—' }}</td>
                    <td class="num">{{ $doc->fecha?->format('d/m/Y') ?? '—' }}</td>
                    @if($esVentas)
                        <td class="num">{{ $doc->fecha_vencimiento?->format('d/m/Y') ?? '—' }}</td>
                        <td class="num">$ {{ number_format($doc->total ?? 0, 0, ',', '.') }}</td>
                        <td class="num">$ {{ number_format($doc->saldo_pendiente ?? 0, 0, ',', '.') }}</td>
                    @else
                        <td class="num">{{ $doc->fecha_vigencia?->format('d/m/Y') ?? '—' }}</td>
                        <td class="num">$ {{ number_format($doc->total ?? 0, 0, ',', '.') }}</td>
                    @endif
                    <td class="num">
                        <span class="badge" style="{{ $bs }}">{{ strtoupper($estadoLabel) }}</span>
                    </td>
                </tr>
                @endforeach
            @else
            <tr>
                <td colspan="7" style="text-align:center; padding:20px; color:#aaa;">Sin registros</td>
            </tr>
            @endif
        </tbody>
    </table>

    <table class="resumen">
        <tr>
            <td>Registros:</td>
            <td class="num">{{ $registros->count() }}</td>
        </tr>
        @if($esVentas)
        <tr>
            <td>Saldo total:</td>
            <td class="num">$ {{ number_format($registros->sum('saldo_pendiente'), 0, ',', '.') }}</td>
        </tr>
        @endif
        <tr class="grand-total">
            <td>TOTAL:</td>
            <td class="num">$ {{ number_format($totalGeneral, 0, ',', '.') }}</td>
        </tr>
    </table>
@endsection
