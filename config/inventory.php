<?php

return [
    /**
     * Configuración de Inventario
     *
     * Controla el comportamiento de movimientos de stock
     * y validaciones críticas del sistema.
     */

    /**
     * ¿Permitir stock negativo?
     *
     * Si es false: Se lanzará error si stock es insuficiente
     * Si es true: Se permite descender por debajo de 0 (no recomendado)
     */
    'allow_negative_stock' => false,

    /**
     * Stock mínimo permitido
     */
    'minimum_stock' => 0,

    /**
     * ¿Permitir restaurar documentos eliminados?
     *
     * false: Se bloquea restore si tiene movimientos asociados
     * true: Se permite pero se marcan inconsistencias
     */
    'allow_restore_with_movements' => false,

    /**
     * Ruta de logs críticos de inventario
     */
    'critical_log_channel' => 'inventory',
];
