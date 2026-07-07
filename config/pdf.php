<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Formato de página
    |--------------------------------------------------------------------------
    | mPDF acepta: 'A4', 'A5', 'letter' (carta), 'legal', '80mm', etc.
    | Para facturación colombiana: 'letter' (216mm x 279mm).
    */
    'format' => 'letter',

    /*
    |--------------------------------------------------------------------------
    | Orientación: P = Portrait (vertical), L = Landscape (horizontal)
    |--------------------------------------------------------------------------
    */
    'orientation' => 'P',

    /*
    |--------------------------------------------------------------------------
    | Márgenes (mm)
    |--------------------------------------------------------------------------
    */
    'margin_left' => 15,
    'margin_right' => 15,
    'margin_top' => 15,
    'margin_bottom' => 15,
    'margin_header' => 5,
    'margin_footer' => 5,

    /*
    |--------------------------------------------------------------------------
    | Tipografía
    |--------------------------------------------------------------------------
    */
    'default_font_size' => 10,
    'default_font' => 'sans-serif',
    'auto_language_detection' => true,

    /*
    |--------------------------------------------------------------------------
    | Metadatos PDF
    |--------------------------------------------------------------------------
    */
    'title' => 'sigaInv',
    'subject' => 'Documento generado por sigaInv',
    'author' => 'sigaInv',

    /*
    |--------------------------------------------------------------------------
    | Directorio temporal para PDFs generados on-the-fly
    |--------------------------------------------------------------------------
    | PdfGeneratorService::limpiarTemporales() borra archivos > 24h aquí.
    */
    'temp_dir' => storage_path('app/tmp/pdf'),

    /*
    |--------------------------------------------------------------------------
    | Watermarks (desactivados por defecto)
    |--------------------------------------------------------------------------
    */
    'show_watermark' => false,
    'show_watermark_image' => false,
    'watermark' => '',
    'watermark_text_alpha' => 0.1,
    'watermark_image_path' => '',
    'watermark_image_alpha' => 0.2,
    'watermark_image_size' => 'D',
    'watermark_image_position' => 'P',
    'watermark_font' => 'sans-serif',

    /*
    |--------------------------------------------------------------------------
    | Display mode
    |--------------------------------------------------------------------------
    | 'fullpage' | 'fullwidth' | 'real' | 'default' (zoom)
    */
    'display_mode' => 'fullpage',

    /*
    |--------------------------------------------------------------------------
    | PDF/A (archivos para archivo de documentos) — desactivado
    |--------------------------------------------------------------------------
    */
    'pdfa' => false,
    'pdfaauto' => false,
    'use_active_forms' => false,

    /*
    |--------------------------------------------------------------------------
    | Custom fonts
    |--------------------------------------------------------------------------
    */
    'custom_font_dir' => '',
    'custom_font_data' => [],

    /*
    |--------------------------------------------------------------------------
    | MODO (utf-8 por defecto)
    |--------------------------------------------------------------------------
    */
    'mode' => 'utf-8',
];
