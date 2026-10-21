<?php
declare(strict_types=1);

namespace PrestamosTec\Inventario\Servicio;

use PrestamosTec\Compartido\ErrorDeNegocio;
use PrestamosTec\Inventario\Dominio\EstadoObjeto;
use PrestamosTec\Inventario\Repositorio\ObjetoRepositorio;

/**
 * Dueño del estado de los equipos (ADR-002).
 *
 * Ningún otro componente cambia objeto.estado: el módulo de Préstamos le pide
 * a este servicio "marca este equipo como prestado" o "libéralo", y aquí se
 * valida contra las transiciones de EstadoObjeto.
 *
 * marcarComoPrestado() y liberarTrasDevolucion() deben llamarse dentro de una
 * Transaccion: el bloqueo de fila dura hasta el commit (ADR-003).
 */
final class InventarioServicio
{
    private ObjetoRepositorio $objetos;

    public function __construct(ObjetoRepositorio $objetos)
    {
        $this->objetos = $objetos;
    }

    public function listarInventario(): array
    {
        return $this->objetos->listarInventario();
    }

    public function listarDisponibles(): array
    {
        return $this->objetos->listarDisponibles();
    }

    /**
     * Bloquea el equipo, verifica que se pueda prestar y lo marca como prestado.
     * Si dos solicitudes llegan a la vez, la segunda espera el bloqueo y, al
     * continuar, ya lee 'prestado' y es rechazada.
     */
    public function marcarComoPrestado(int $idObjeto): void
    {
        $objeto = $this->obtenerBloqueado($idObjeto);
        if (!EstadoObjeto::permitePrestamo($objeto['estado'])) {
            throw new ErrorDeNegocio(
                "El equipo \"{$objeto['nombre']}\" no está disponible (estado actual: {$objeto['estado']}).",
                ErrorDeNegocio::CONFLICTO
            );
        }
        $this->objetos->actualizarEstado($idObjeto, EstadoObjeto::PRESTADO);
    }

    /**
     * Devuelve el equipo a 'disponible' cuando termina su préstamo.
     * Si alguien lo pasó manualmente a mantenimiento o baja, se respeta ese estado.
     */
    public function liberarTrasDevolucion(int $idObjeto): void
    {
        $objeto = $this->obtenerBloqueado($idObjeto);
        if ($objeto['estado'] === EstadoObjeto::PRESTADO) {
            $this->objetos->actualizarEstado($idObjeto, EstadoObjeto::DISPONIBLE);
        }
    }

    /** Cambio de estado manual (mantenimiento, baja...), validando la transición. */
    public function cambiarEstado(int $idObjeto, string $nuevoEstado): void
    {
        if (!EstadoObjeto::esValido($nuevoEstado)) {
            throw new ErrorDeNegocio("Estado desconocido: {$nuevoEstado}.", ErrorDeNegocio::DATOS_INVALIDOS);
        }
        $objeto = $this->obtenerBloqueado($idObjeto);
        if (!EstadoObjeto::puedeTransicionar($objeto['estado'], $nuevoEstado)) {
            throw new ErrorDeNegocio(
                "No se puede pasar el equipo de '{$objeto['estado']}' a '{$nuevoEstado}'.",
                ErrorDeNegocio::CONFLICTO
            );
        }
        $this->objetos->actualizarEstado($idObjeto, $nuevoEstado);
    }

    private function obtenerBloqueado(int $idObjeto): array
    {
        $objeto = $this->objetos->buscarYBloquear($idObjeto);
        if ($objeto === null) {
            throw new ErrorDeNegocio("El equipo #{$idObjeto} no existe.", ErrorDeNegocio::NO_ENCONTRADO);
        }
        return $objeto;
    }
}
