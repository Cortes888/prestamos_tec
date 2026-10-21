<?php
declare(strict_types=1);

namespace PrestamosTec\Config;

use PDO;

/**
 * Única fábrica de conexiones de la nueva arquitectura.
 *
 * Reemplaza progresivamente a src/db.php (que sigue existiendo para los
 * endpoints que aún no se han migrado). Los valores por defecto son los
 * mismos de db.php; se pueden sobrescribir con variables de entorno.
 */
final class Conexion
{
    private static ?PDO $pdo = null;

    public static function obtener(): PDO
    {
        if (self::$pdo === null) {
            $host = getenv('DB_HOST') ?: 'localhost';
            $puerto = getenv('DB_PORT') ?: '3306';
            $base = getenv('DB_NAME') ?: 'prestamos_tec';
            $usuario = getenv('DB_USER') ?: 'root';
            $clave = getenv('DB_PASS') !== false ? (string) getenv('DB_PASS') : '';

            self::$pdo = new PDO(
                "mysql:host={$host};port={$puerto};dbname={$base};charset=utf8mb4",
                $usuario,
                $clave,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
        }
        return self::$pdo;
    }
}
