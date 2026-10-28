<?php

/*
| Datos del negocio que se muestran al cliente (menú público).
| "Tabla" (APP_NAME) es el nombre del sistema; esto es el nombre de la charcutería.
| Uso: config('negocio.nombre')
*/

return [
    'nombre' => env('NEGOCIO_NOMBRE', 'La Charcutería'),

    // Logo con fondo transparente, en el rojo del logo y en crema para fondos oscuros
    'logo' => 'img/logo.png',
    'logo_crema' => 'img/logo-crema.png',
];
