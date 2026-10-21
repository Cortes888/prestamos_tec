<?php
declare(strict_types=1);

namespace PrestamosTec\Compartido;

use PrestamosTec\Config\Conexion;
use PrestamosTec\Inventario\Repositorio\MySqlObjetoRepositorio;
use PrestamosTec\Inventario\Servicio\InventarioServicio;
use PrestamosTec\Prestamos\Repositorio\MySqlPrestamoRepositorio;
use PrestamosTec\Prestamos\Servicio\PrestamoServicio;

/**
 * Arma los servicios con sus dependencias reales (MySQL).
 * Las pruebas arman los mismos servicios con dobles en memoria.
 */
final class Contenedor
{
    public static function inventario(): InventarioServicio
    {
        return new InventarioServicio(new MySqlObjetoRepositorio(Conexion::obtener()));
    }

    public static function prestamos(): PrestamoServicio
    {
        $pdo = Conexion::obtener();
        return new PrestamoServicio(
            new MySqlPrestamoRepositorio($pdo),
            self::inventario(),
            new PdoTransaccion($pdo)
        );
    }
}
