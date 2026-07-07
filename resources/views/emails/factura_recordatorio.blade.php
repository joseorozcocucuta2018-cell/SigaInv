<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        body      { font-family: Arial, sans-serif; font-size: 14px; color: #333; background: #f4f6f9; margin: 0; padding: 0; }
        .wrapper  { max-width: 600px; margin: 30px auto; background: #fff; border-radius: 6px; overflow: hidden;
                    box-shadow: 0 2px 8px rgba(0,0,0,.08); }
        .header   { background: #1a3c6e; color: #fff; padding: 24px 30px; }
        .header.urgente { background: #c53030; }
        .header h1 { margin: 0; font-size: 20px; }
        .header p  { margin: 4px 0 0; font-size: 13px; opacity: .8; }
        .body     { padding: 28px 30px; }
        .badge    { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 700;
                    margin-bottom: 16px; }
        .badge-warning { background: #fefcbf; color: #744210; border: 1px solid #f6e05e; }
        .badge-danger  { background: #fff5f5; color: #c53030; border: 1px solid #fc8181; }
        .summary  { background: #f7fafc; border-left: 4px solid #1a3c6e; padding: 14px 16px;
                    border-radius: 0 4px 4px 0; margin: 18px 0; }
        .summary.urgente { border-left-color: #c53030; }
        .summary table { width: 100%; border-collapse: collapse; }
        .summary td    { padding: 5px 0; font-size: 13px; }
        .summary .label { color: #718096; width: 150px; }
        .summary .value { color: #1a202c; font-weight: 600; }
        .value-danger   { color: #c53030 !important; }
        .cta      { text-align: center; margin: 24px 0; }
        .note     { font-size: 12px; color: #718096; margin-top: 20px; border-top: 1px solid #e2e8f0; padding-top: 14px; }
        .footer   { background: #f7fafc; padding: 14px 30px; text-align: center;
                    font-size: 11px; color: #a0aec0; border-top: 1px solid #e2e8f0; }
    </style>
</head>
<body>
<div class="wrapper">

    <div class="header {{ $tipo === 'vencida' ? 'urgente' : '' }}">
        <h1>
            @if($tipo === 'vencida')
                Factura Vencida
            @else
                Recordatorio de Pago
            @endif
        </h1>
        <p>{{ $empresa?->razon_social ?? 'sigaInv' }}</p>
    </div>

    <div class="body">
        <p>Estimado(a) <strong>{{ $venta->cliente?->nombre ?? 'cliente' }}</strong>,</p>

        @if($tipo === 'por_vencer')
            <span class="badge badge-warning">Vence en {{ $diasRestantes }} día(s)</span>
            <p>Le recordamos que la siguiente factura está próxima a vencer:</p>
        @else
            <span class="badge badge-danger">
                @if($diasRestantes === 0)
                    Vence hoy
                @else
                    {{ abs($diasRestantes) }} día(s) de mora
                @endif
            </span>
            <p>Le informamos que la siguiente factura se encuentra <strong>vencida</strong> y requiere atención inmediata:</p>
        @endif

        <div class="summary {{ $tipo === 'vencida' ? 'urgente' : '' }}">
            <table>
                <tr>
                    <td class="label">Factura N°:</td>
                    <td class="value">{{ $venta->numero }}</td>
                </tr>
                <tr>
                    <td class="label">Fecha:</td>
                    <td class="value">{{ $venta->fecha?->format('d/m/Y') }}</td>
                </tr>
                <tr>
                    <td class="label">Fecha vencimiento:</td>
                    <td class="value {{ $tipo === 'vencida' ? 'value-danger' : '' }}">
                        {{ $venta->fecha_vencimiento?->format('d/m/Y') }}
                    </td>
                </tr>
                <tr>
                    <td class="label">Total factura:</td>
                    <td class="value">${{ number_format($venta->total_confirmado ?? $venta->total, 2) }}</td>
                </tr>
                <tr>
                    <td class="label">Saldo pendiente:</td>
                    <td class="value value-danger">${{ number_format($venta->saldo_pendiente, 2) }}</td>
                </tr>
            </table>
        </div>

        @if($tipo === 'vencida')
            <p>Para regularizar su situación, por favor comuníquese con nosotros o realice su pago a la brevedad.</p>
        @else
            <p>Para evitar inconvenientes, le invitamos a realizar el pago antes de la fecha de vencimiento.</p>
        @endif

        <p class="note">
            Si ya realizó el pago, ignore este mensaje o comuníquese con nosotros para confirmarlo.<br>
            Este es un mensaje automático generado por {{ $empresa?->nombre_comercial ?? $empresa?->razon_social ?? 'sigaInv' }}.
        </p>
    </div>

    <div class="footer">
        {{ $empresa?->razon_social ?? 'sigaInv' }}
        @if($empresa?->telefono) &nbsp;|&nbsp; Tel: {{ $empresa->telefono }} @endif
        @if($empresa?->email) &nbsp;|&nbsp; {{ $empresa->email }} @endif
    </div>

</div>
</body>
</html>
