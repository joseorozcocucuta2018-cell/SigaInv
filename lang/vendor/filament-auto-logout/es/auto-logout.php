<?php

return [
    'notification' => [
        'title' => 'Tu sesión está a punto de expirar',
        // El cuerpo de la notificación reemplazará :timeleft: con el tiempo restante.
        // Puedes eliminarlo si no deseas mostrarlo.
        'body' => 'Serás desconectado en :timeleft:',
    ],

    'units' => [
        'seconds' => [
            'short' => 's',
            'long' => 'segundos',
        ],
        'minutes' => [
            'short' => 'min',
            'long' => 'minutos',
        ],
        'hours' => [
            'short' => 'h',
            'long' => 'horas',
        ],
    ],
];
