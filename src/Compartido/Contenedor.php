<?php
declare(strict_types=1);

namespace PrestamosTec\Compartido;

use PrestamosTec\Config\Conexion;
use PrestamosTec\Inventario\Repositorio\MySqlObjetoRepositorio;
use PrestamosTec\Inventario\Servicio\InventarioServicio;
use PrestamosTec\Prestamos\Repositorio\MySqlPrestamoRepositorio;
use PrestamosTec\Prestamos\Servicio\PrestamoServicio;

// Crea los servicios que usan los controladores
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
