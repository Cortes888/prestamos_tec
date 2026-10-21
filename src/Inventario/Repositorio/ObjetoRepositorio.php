<?php
declare(strict_types=1);

namespace PrestamosTec\Inventario\Repositorio;

/**
 * Acceso a datos de la tabla objeto. Es el único lugar donde se escribe SQL
 * sobre los equipos (ADR-001).
 */
interface ObjetoRepositorio
{
    /** Inventario visible (sin dados de baja), con categoría y total de préstamos. */
    public function listarInventario(): array;

    /** Solo equipos en estado 'disponible' (para el selector de préstamos). */
    public function listarDisponibles(): array;

    /**
     * Lee el equipo y bloquea su fila hasta que termine la transacción
     * (SELECT ... FOR UPDATE). Devuelve null si no existe.
     *
     * @return array{id_objeto:int, nombre:string, estado:string}|null
     */
    public function buscarYBloquear(int $idObjeto): ?array;

    public function actualizarEstado(int $idObjeto, string $estado): void;
}
