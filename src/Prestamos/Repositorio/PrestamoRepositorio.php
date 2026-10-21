<?php
declare(strict_types=1);

namespace PrestamosTec\Prestamos\Repositorio;

/**
 * Acceso a datos de la tabla prestamo. Antes estas consultas estaban
 * copiadas en prestamos.php, dashboard.php y notificaciones.php (ADR-001).
 */
interface PrestamoRepositorio
{
    /** Todos los préstamos con nombre de usuario y de objeto (vista de préstamos). */
    public function listarConDetalle(): array;

    public function usuarioActivoExiste(int $idUsuario): bool;

    /**
     * Inserta un préstamo 'activo' y devuelve su id.
     * Debe lanzar ErrorDeNegocio si la base de datos rechaza un segundo
     * préstamo activo para el mismo equipo (índice único de BDSQL V3.sql).
     */
    public function crear(int $idUsuario, int $idObjeto, int $dias, string $observaciones): int;

    /**
     * Lee el préstamo y bloquea su fila hasta el fin de la transacción.
     *
     * @return array{id_prestamo:int, id_objeto:int, estado:string}|null
     */
    public function buscarYBloquear(int $idPrestamo): ?array;

    /** Marca el préstamo como devuelto y devuelve cuántas filas cambió. */
    public function marcarDevuelto(int $idPrestamo, string $observaciones): int;
}
