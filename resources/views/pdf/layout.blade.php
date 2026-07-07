@php
    $empresa = $empresa ?? null;
    $logoUrl = null;
    if ($empresa?->logo_impresion) {
        try { $logoUrl = \Illuminate\Support\Facades\Storage::disk('directo')->url($empresa->logo_impresion); }
        catch (\Throwable) {}
    }
    if (! $logoUrl && $empresa?->logo) {
        try { $logoUrl = \Illuminate\Support\Facades\Storage::disk('directo')->url($empresa->logo); }
        catch (\Throwable) {}
    }
    $iniciales = '';
    foreach (explode(' ', $empresa?->razon_social ?? '') as $w) {
        if ($w && strlen($iniciales) < 2) $iniciales .= strtoupper($w[0]);
    }
    if (! $iniciales) $iniciales = 'SI';
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @hasSection('title')
        <title>@yield('title')</title>
    @endif
    <style>
        @page { margin: 10mm; size: letter portrait; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 10px; color: #333; line-height: 1.4; }
        @yield('styles')
    </style>
</head>
<body>
    <table style="width:100%; border:none; margin-bottom:10px;">
        <tr>
            <td style="border:none; padding:0; width:60%;">
                @if($logoUrl)
                    <img src="{{ $logoUrl }}" alt="Logo" style="width:90px; height:45px; object-fit:contain;">
                @else
                    <div style="width:45px; height:45px; background:#1e3a5f; color:white; border-radius:6px; display:flex; align-items:center; justify-content:center; font-weight:bold; font-size:16px;">{{ $iniciales }}</div>
                @endif
                <div style="margin-top:6px;">
                    <strong style="font-size:11px;">{{ $empresa?->razon_social ?? 'Mi Empresa' }}</strong>
                    <div style="font-size:8px; color:#666;">
                        NIT: {{ $empresa?->nit ?? '' }}{{ $empresa?->digito_verificacion ? '-'.$empresa->digito_verificacion : '' }}
                        @if($empresa?->direccion) · {{ $empresa->direccion }} @endif
                        @if($empresa?->ciudad) · {{ $empresa->ciudad->nombre }} @endif
                        @if($empresa?->telefono) · Tel: {{ $empresa->telefono }} @endif
                    </div>
                </div>
            </td>
            <td style="border:none; padding:0; text-align:right; vertical-align:top;">
                @yield('header-right')
                <div style="font-size:8px; color:#999; margin-top:2px;">{{ now()->format('d/m/Y H:i') }}</div>
            </td>
        </tr>
    </table>

    <hr style="border:none; border-top:2px solid #1e3a5f; margin:8px 0;">

    @yield('content')

    <div style="margin-top:20px; text-align:center; font-size:8px; color:#aaa; border-top:1px solid #eee; padding-top:6px;">
        @yield('footer')
        {{ $empresa?->razon_social ?? '' }} — Documento generado el {{ now()->format('d/m/Y \a \l\a\s H:i') }}
        @if($empresa?->sitio_web) · {{ $empresa->sitio_web }} @endif
    </div>
</body>
</html>
