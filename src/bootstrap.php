<?php
/**
 * Punto de arranque de la nueva arquitectura por capas (Semana 13).
 *
 * Registra un autoloader sencillo: la clase PrestamosTec\Inventario\Servicio\InventarioServicio
 * se busca en src/Inventario/Servicio/InventarioServicio.php.
 * No requiere Composer, para que funcione igual en XAMPP.
 */
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
