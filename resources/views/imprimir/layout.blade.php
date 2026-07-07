<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $titulo ?? 'Reporte' }} — {{ $empresa?->razon_social ?? 'SigaInv' }}</title>
    <style>
        /* ═══ Toolbar de impresión ═══ */
        .print-toolbar {
            position: sticky;
            top: 0;
            z-index: 1000;
            background: #1e293b;
            color: white;
            padding: 12px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-family: system-ui, -apple-system, sans-serif;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        }
        .print-toolbar .toolbar-title {
            font-size: 16px;
            font-weight: 600;
        }
        .print-toolbar .toolbar-actions {
            display: flex;
            gap: 10px;
        }
        .print-btn {
            background: #3b82f6;
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: background 0.2s;
        }
        .print-btn:hover { background: #2563eb; }
        .back-btn {
            background: transparent;
            color: #cbd5e1;
            border: 1px solid #475569;
            padding: 8px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s;
        }
        .back-btn:hover { color: white; border-color: #94a3b8; }

        /* ═══ Contenido del documento ═══ */
        .document-wrapper {
            max-width: 1000px; /* Un poco más ancho para reportes */
            margin: 0 auto;
            padding: 24px;
            background: white;
        }

        /* ═══ Reglas de impresión ═══ */
        @media print {
            .print-toolbar { display: none !important; }
            .document-wrapper {
                max-width: 100%;
                padding: 0;
                margin: 0;
            }
            @page {
                margin: 10mm;
                size: {{ $orientacion ?? 'letter portrait' }};
            }
        }
    </style>
</head>
<body style="margin:0; background:#f1f5f9; font-family: system-ui, -apple-system, sans-serif;">

    {{-- ═══ BARRA DE HERRAMIENTAS ═══ --}}
    <div class="print-toolbar">
        <span class="toolbar-title">{{ $empresa?->razon_social ?? 'SigaInv' }} — {{ $titulo ?? 'Reporte' }}</span>
        <div class="toolbar-actions">
            <button class="back-btn" onclick="window.close()">&#x2715; Cerrar</button>
            <button class="print-btn" onclick="window.print()">&#x1F5A8; Imprimir</button>
        </div>
    </div>

    {{-- ═══ CONTENIDO ═══ --}}
    <div class="document-wrapper">
        @yield('content')
    </div>

</body>
</html>
