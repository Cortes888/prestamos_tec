<?php
// Carga automática de las clases de src/ (sin Composer)
declare(strict_types=1);

spl_autoload_register(function (string $clase): void {
    $prefijo = 'PrestamosTec\\';
    if (strncmp($clase, $prefijo, strlen($prefijo)) !== 0) {
        return;
    }
    $relativa = str_replace('\\', DIRECTORY_SEPARATOR, substr($clase, strlen($prefijo)));
    $ruta = __DIR__ . DIRECTORY_SEPARATOR . $relativa . '.php';
    if (is_file($ruta)) {
        require $ruta;
    }
});
