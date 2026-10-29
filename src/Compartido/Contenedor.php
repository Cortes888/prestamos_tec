<?php
declare(strict_types=1);

namespace PrestamosTec\Compartido;

use PrestamosTec\Config\Conexion;
use PrestamosTec\Inventario\Repositorio\MySqlObjetoRepositorio;
use PrestamosTec\Inventario\Servicio\InventarioServicio;
use PrestamosTec\Prestamos\Repositorio\MySqlPrestamoRepositorio;
use PrestamosTec\Prestamos\Servicio\PrestamoServicio;

/**
 * Arma los servicios con sus dependencias reales (MySQL), para que los
 * controladores no tengan que hacerlo (inyección de dependencias).
 */
final class Contenedor
{
    public static function inventario(): InventarioServicio
    {
        $pdo = Conexion::obtener();
        return new InventarioServicio(new MySqlObjetoRepositorio($pdo), new PdoTransaccion($pdo));
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
