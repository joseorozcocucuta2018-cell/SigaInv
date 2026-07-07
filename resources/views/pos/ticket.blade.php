<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ticket - {{ $numero }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Courier New', monospace; font-size: 11px; width: 280px; }
        .center { text-align: center; }
        .right { text-align: right; }
        .bold { font-weight: bold; }
        .line { border-top: 1px dashed #000; margin: 6px 0; }
        @media print { body { width: 280px !important; } .no-print { display: none; } }
    </style>
</head>
<body onload="window.print()">
{{-- Empresa --}}
<div class="center bold">{{ $empresa['nombre'] ?? 'EMPRESA' }}</div>
<div class="center">NIT: {{ $empresa['nit'] ?? '000000000-0' }}</div>
@if(!empty($empresa['direccion']))
<div class="center">{{ $empresa['direccion'] }}</div>
@endif
@if(!empty($empresa['telefono']))
<div class="center">Tel: {{ $empresa['telefono'] }}</div>
@endif

<div class="line"></div>
<div class="center bold">{{ strtoupper($tipoDocumento) }} N: {{ $numero }}</div>
<div class="center">{{ $fecha }}</div>
<div>Cajero: {{ $cajero }}</div>
<div>Cliente: {{ $cliente['nombre'] ?? 'Consumidor Final' }}</div>

<div class="line"></div>
<div><span>Producto</span><span class="right">Cant</span><span class="right">Prix</span><span class="right">Subt</span></div>
<div class="line"></div>

@foreach($items as $item)
<div>
<span>{{ str_limit($item['nombre'], 16) }}</span>
<span class="right">{{ $item['cantidad'] }}</span>
<span class="right">{{ $item['precio'] }}</span>
<span class="right">{{ $item['subtotal'] }}</span>
</div>
@endforeach

<div class="line"></div>
<div><span>Subtotal:</span><span class="right">{{ $subtotal }}</span></div>
@if($impuesto > 0)
<div><span>IVA:</span><span class="right">{{ $impuesto }}</span></div>
@endif
<div class="line"></div>
<div><span class="bold">TOTAL:</span><span class="right bold">{{ $total }}</span></div>

<div class="line"></div>
<div class="center">Gracias por su preferencia</div>
<div class="center">Vuelva pronto</div>

<div class="no-print center" style="margin-top:10px">
<button onclick="window.print()">Imprimir</button>
</div>
</body>
</html>