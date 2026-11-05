<?php
declare(strict_types=1);

namespace PrestamosTec\Inventario\Repositorio;

// Consultas de la tabla objeto
interface ObjetoRepositorio
{
    // No incluye los equipos dados de baja
    public function listarInventario(): array;

    public function listarDisponibles(): array;

    // Lee el equipo con SELECT ... FOR UPDATE para bloquearlo
    public function buscarYBloquear(int $idObjeto): ?array;

    public function actualizarEstado(int $idObjeto, string $estado): void;
}
