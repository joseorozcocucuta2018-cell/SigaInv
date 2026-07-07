@extends('pdf.layout')

@section('title')
    Kardex — {{ $producto->nombre }}
@endsection

@section('styles')
    @page { size: letter landscape; margin: 10mm; }
    @media print { .badge { break-inside: avoid; } }
    .producto-info { background: #f0f4ff; padding: 8px 12px; border-radius: 6px; margin-bottom: 10px; }
    .producto-info span { margin-right: 20px; }
    .filtros { font-size: 9px; color: #666; margin-bottom: 8px; }
    table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
    thead th { background: #2563eb; color: white; padding: 5px 4px; text-align: left; font-size: 9px; text-transform: uppercase; }
    tbody td { padding: 4px; border-bottom: 1px solid #e5e7eb; font-size: 9px; }
    tbody tr:nth-child(even) { background: #f9fafb; }
    .text-right { text-align: right; }
    .text-center { text-align: center; }
    .badge { display: inline-block; padding: 1px 6px; border-radius: 8px; font-size: 8px; font-weight: bold; }
    .badge-entrada { background: #dcfce7; color: #166534; }
    .badge-salida { background: #fee2e2; color: #991b1b; }
    .badge-ajuste { background: #fef3c7; color: #92400e; }
    .badge-otro { background: #e5e7eb; color: #374151; }
    .bold { font-weight: bold; }
@endsection

@section('header-right')
    <div style="font-size:16px; font-weight:bold; color:#1e40af;">KARDEX</div>
@endsection

@section('content')
    <div class="producto-info">
        <strong>{{ $producto->nombre }}</strong>
        <span>| Código: {{ $producto->codigo ?? 'N/A' }}</span>
        <span>| Categoría: {{ $producto->categoria?->nombre ?? 'N/A' }}</span>
        <span>| U.M.: {{ $producto->unidadMedida?->nombre ?? 'N/A' }}</span>
        <span>| CPP: ${{ number_format($producto->costo_promedio ?? 0, 2, ',', '.') }}</span>
    </div>

    @if($filtros)
        <div class="filtros">
            Filtros:
            @if($filtros['bodega'] ?? null) Bodega: {{ $filtros['bodega'] }} | @endif
            @if($filtros['desde'] ?? null) Desde: {{ $filtros['desde'] }} | @endif
            @if($filtros['hasta'] ?? null) Hasta: {{ $filtros['hasta'] }} @endif
        </div>
    @endif

    <table>
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Bodega</th>
                <th>Tipo Movimiento</th>
                <th class="text-right">Cantidad</th>
                <th class="text-right">Costo Unit.</th>
                <th class="text-right">Stock Result.</th>
                <th>Doc. Tipo</th>
                <th>Observación</th>
            </tr>
        </thead>
        <tbody>
            @if(count($movimientos) > 0)
                @foreach ($movimientos as $mov)
                    @php
                        $esEntrada = str_starts_with($mov->tipo_movimiento, 'entrada') || in_array($mov->tipo_movimiento, ['reverso_anulacion', 'ajuste_inicial', 'ajuste_positivo']);
                        $esSalida = str_starts_with($mov->tipo_movimiento, 'salida') || $mov->tipo_movimiento === 'ajuste_negativo';
                        $badgeClass = $esEntrada ? 'badge-entrada' : ($esSalida ? 'badge-salida' : 'badge-otro');
                        $tipoLabel = match($mov->tipo_movimiento) {
                            'entrada_compra' => 'Entrada Compra',
                            'salida_venta' => 'Salida Venta',
                            'salida_remision' => 'Salida Remisión',
                            'entrada_devolucion' => 'Entrada Devolución',
                            'salida_devolucion' => 'Salida Devolución',
                            'salida_anulacion_devolucion' => 'Anul. Devolución',
                            'traslado_entrada', 'entrada_traslado' => 'Entrada Traslado',
                            'traslado_salida', 'salida_traslado' => 'Salida Traslado',
                            'reverso_traslado' => 'Reverso Traslado',
                            'reverso_anulacion' => 'Reverso Anulación',
                            'ajuste_inicial' => 'Ajuste Inicial',
                            'ajuste_conteo' => 'Ajuste Conteo',
                            'ajuste_positivo' => 'Ajuste (+)',
                            'ajuste_negativo' => 'Ajuste (-)',
                            'facturacion_remision' => 'Facturación Rem.',
                            'anulacion_venta_remision' => 'Anul. Vta/Rem',
                            'ajuste_costo_promedio' => 'Ajuste CPP',
                            default => $mov->tipo_movimiento,
                        };
                    @endphp
                    <tr>
                        <td>{{ $mov->fecha_movimiento?->format('d/m/Y H:i') }}</td>
                        <td>{{ $mov->bodega?->nombre ?? '-' }}</td>
                        <td><span class="badge {{ $badgeClass }}">{{ $tipoLabel }}</span></td>
                        <td class="text-right">{{ number_format($mov->cantidad, 3, ',', '.') }}</td>
                        <td class="text-right">${{ number_format($mov->costo_unitario, 2, ',', '.') }}</td>
                        <td class="text-right bold">{{ number_format($mov->stock_resultante, 3, ',', '.') }}</td>
                        <td>{{ $mov->documento_tipo ? ucfirst($mov->documento_tipo) : '-' }}</td>
                        <td>{{ Str::limit($mov->observacion, 50) }}</td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="8" class="text-center" style="padding: 20px; color: #999;">No hay movimientos registrados</td>
                </tr>
            @endif
        </tbody>
    </table>

    <div style="font-size:9px; color:#666; margin-top:4px;">
        Total movimientos: {{ $movimientos->count() }}
    </div>
@endsection
