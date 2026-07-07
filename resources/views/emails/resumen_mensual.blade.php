<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        body      { font-family: Arial, sans-serif; font-size: 14px; color: #333; background: #f4f6f9; margin: 0; padding: 0; }
        .wrapper  { max-width: 640px; margin: 30px auto; background: #fff; border-radius: 6px; overflow: hidden;
                    box-shadow: 0 2px 8px rgba(0,0,0,.08); }
        .header   { background: #1a3c6e; color: #fff; padding: 24px 30px; }
        .header h1 { margin: 0; font-size: 20px; }
        .header p  { margin: 4px 0 0; font-size: 13px; opacity: .8; }
        .body     { padding: 28px 30px; }
        h2        { font-size: 15px; color: #1a3c6e; border-bottom: 2px solid #e2e8f0; padding-bottom: 6px; margin-top: 24px; }
        table     { width: 100%; border-collapse: collapse; font-size: 13px; margin-top: 8px; }
        th        { background: #edf2f7; color: #4a5568; text-align: left; padding: 7px 10px; font-weight: 600; }
        td        { padding: 7px 10px; border-bottom: 1px solid #e2e8f0; color: #2d3748; }
        tr:last-child td { border-bottom: none; }
        .text-right { text-align: right; }
        .saldo-box  { background: #fff5f5; border: 1px solid #fc8181; border-radius: 6px;
                      padding: 14px 18px; margin-top: 20px; }
        .saldo-box.ok { background: #f0fff4; border-color: #68d391; }
        .saldo-label  { font-size: 13px; color: #718096; }
        .saldo-value  { font-size: 22px; font-weight: 700; color: #c53030; }
        .saldo-value.ok { color: #276749; }
        .note     { font-size: 12px; color: #718096; margin-top: 20px; border-top: 1px solid #e2e8f0; padding-top: 14px; }
        .footer   { background: #f7fafc; padding: 14px 30px; text-align: center;
                    font-size: 11px; color: #a0aec0; border-top: 1px solid #e2e8f0; }
        .empty    { color: #a0aec0; font-style: italic; font-size: 13px; padding: 6px 0; }
    </style>
</head>
<body>
<div class="wrapper">

    <div class="header">
        <h1>Estado de cuenta — {{ $mes }}</h1>
        <p>{{ $empresa?->razon_social ?? 'SigaWeb' }}</p>
    </div>

    <div class="body">
        <p>Estimado(a) <strong>{{ $cliente->nombre }}</strong>,</p>
        <p>Le enviamos el resumen de su cuenta correspondiente al mes de <strong>{{ $mes }}</strong>.</p>

        {{-- Facturas del mes --}}
        <h2>Facturas del mes</h2>
        @if($ventas->isNotEmpty())
            <table>
                <thead>
                    <tr>
                        <th>N° Factura</th>
                        <th>Fecha</th>
                        <th class="text-right">Total</th>
                        <th class="text-right">Saldo</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($ventas as $venta)
                        <tr>
                            <td>{{ $venta->numero }}</td>
                            <td>{{ $venta->fecha?->format('d/m/Y') }}</td>
                            <td class="text-right">${{ number_format($venta->total_confirmado ?? $venta->total, 0, ',', '.') }}</td>
                            <td class="text-right">${{ number_format($venta->saldo_pendiente, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p class="empty">No hubo facturas en este período.</p>
        @endif

        {{-- Pagos del mes --}}
        <h2>Pagos recibidos</h2>
        @if($pagos->isNotEmpty())
            <table>
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Recibo</th>
                        <th class="text-right">Monto</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pagos as $pago)
                        <tr>
                            <td>{{ $pago->fecha?->format('d/m/Y') }}</td>
                            <td>{{ $pago->numero }}</td>
                            <td class="text-right">${{ number_format($pago->monto, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p class="empty">No se registraron pagos en este período.</p>
        @endif

        {{-- Devoluciones --}}
        @if($devoluciones->isNotEmpty())
            <h2>Devoluciones</h2>
            <table>
                <thead>
                    <tr>
                        <th>N° Devolución</th>
                        <th>Fecha</th>
                        <th class="text-right">Valor</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($devoluciones as $dev)
                        <tr>
                            <td>{{ $dev->numero ?? '—' }}</td>
                            <td>{{ $dev->created_at?->format('d/m/Y') }}</td>
                            <td class="text-right">${{ number_format($dev->total, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        {{-- Saldo final --}}
        <div class="saldo-box {{ $saldoPendiente <= 0 ? 'ok' : '' }}">
            <div class="saldo-label">Saldo pendiente actual:</div>
            <div class="saldo-value {{ $saldoPendiente <= 0 ? 'ok' : '' }}">
                ${{ number_format($saldoPendiente, 0, ',', '.') }}
            </div>
        </div>

        <p class="note">
            Este resumen es informativo. Si tiene alguna pregunta, comuníquese con nosotros.<br>
            Mensaje automático generado por {{ $empresa?->nombre_comercial ?? $empresa?->razon_social ?? 'SigaWeb' }}.
        </p>
    </div>

    <div class="footer">
        {{ $empresa?->razon_social ?? 'SigaWeb' }}
        @if($empresa?->telefono) &nbsp;|&nbsp; Tel: {{ $empresa->telefono }} @endif
        @if($empresa?->email) &nbsp;|&nbsp; {{ $empresa->email }} @endif
    </div>

</div>
</body>
</html>
