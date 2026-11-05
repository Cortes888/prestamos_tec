<?php
declare(strict_types=1);

namespace PrestamosTec\Prestamos\Repositorio;

// Consultas de la tabla prestamo
interface PrestamoRepositorio
{
    public function listarConDetalle(): array;

    public function usuarioActivoExiste(int $idUsuario): bool;

    public function crear(int $idUsuario, int $idObjeto, int $dias, string $observaciones): int;

    // Lee el préstamo con SELECT ... FOR UPDATE
    public function buscarYBloquear(int $idPrestamo): ?array;

    // Devuelve cuántas filas cambió
    public function marcarDevuelto(int $idPrestamo, string $observaciones): int;
}
