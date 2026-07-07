<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Configuración del Punto de Venta (POS)
|--------------------------------------------------------------------------
|
| Flags de comportamiento del POS. La regla inquebrantable "Anulación/
| Devoluciones en POS: NO existen" hace innecesario configurar un modo
| de anulación aquí. Si en el futuro se habilita, los flags se introducen
| en ese momento.
|
| CORS para subdominios dedicados se configura en config/cors.php,
| NO aquí (Laravel estándar).
|
*/

return [

    /*
    |--------------------------------------------------------------------------
    | Pago mixto
    |--------------------------------------------------------------------------
    |
    | Habilita el endpoint de finalizar venta/remisión con pagos múltiples
    | (efectivo + transferencia, etc.). El backend valida la regla de
    | "no-efectivo no puede exceder el déficit cubierto por efectivo".
    | Si se desactiva, el POS solo permite pago único de una forma.
    */
    'pago_mixto_habilitado' => (bool) env('POS_PAGO_MIXTO', true),

    /*
    |--------------------------------------------------------------------------
    | Ruta de imágenes de productos
    |--------------------------------------------------------------------------
    |
    | Prefijo público para resolver las imágenes de productos devueltas
    | en el listado del POS. Se concatena con el path almacenado en
    | productos.imagen (disco 'public' o 'directo' según configuración).
    */
    'imagenes_path' => env('POS_IMAGES_PATH', '/storage/productos'),

    /*
    |--------------------------------------------------------------------------
    | Roles autorizados a usar el POS
    |--------------------------------------------------------------------------
    |
    | Roles de Spatie que pueden autenticarse en el POS y operar caja.
    | El middleware EnsurePosAccess valida contra esta lista.
    */
    'roles_autorizados' => ['vendedor', 'administrador'],

    /*
    |--------------------------------------------------------------------------
    | Versión del frontend del POS
    |--------------------------------------------------------------------------
    |
    | Se envía en el endpoint /pos/api/config para que el JS pueda detectar
    | actualizaciones y forzar recarga. Incrementar cuando se publican
    | cambios incompatibles en el JS del POS.
    */
    'frontend_version' => env('POS_FRONTEND_VERSION', '1.0.0'),

];
