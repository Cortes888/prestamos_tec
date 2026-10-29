<?php
declare(strict_types=1);

namespace PrestamosTec\Inventario\Servicio;

use PrestamosTec\Compartido\ErrorDeNegocio;
use PrestamosTec\Compartido\Transaccion;
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
 * Transaccion: el bloqueo de fila dura hasta el commit (ADR-003). Esa
 * transacción la abre PrestamoServicio.
 *
 * cambiarEstado() es el cambio manual que llega por la API
 * (PATCH /api/objetos/{id}/estado) y abre su propia transacción.
 */
final class InventarioServicio
{
    private ObjetoRepositorio $objetos;
    private ?Transaccion $transaccion;

    public function __construct(ObjetoRepositorio $objetos, ?Transaccion $transaccion = null)
    {
        $this->objetos = $objetos;
        $this->transaccion = $transaccion;
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

    /**
     * Cambio de estado manual: mantenimiento, baja o disponible.
     *
     * El estado 'prestado' no se pone ni se quita por aquí: solo cambia al
     * registrar o devolver un préstamo. Así nunca queda un equipo prestado sin
     * préstamo abierto, ni un préstamo abierto con el equipo disponible.
     *
     * @return array{id_objeto:int, estado_anterior:string, estado:string}
     */
    public function cambiarEstado(int $idObjeto, string $nuevoEstado): array
    {
        if ($idObjeto <= 0) {
            throw new ErrorDeNegocio('Falta el número del equipo.', ErrorDeNegocio::DATOS_INVALIDOS);
        }
        if (!EstadoObjeto::esValido($nuevoEstado)) {
            throw new ErrorDeNegocio(
                "Estado desconocido: '{$nuevoEstado}'. Use mantenimiento, baja o disponible.",
                ErrorDeNegocio::DATOS_INVALIDOS
            );
        }
        if ($nuevoEstado === EstadoObjeto::PRESTADO) {
            throw new ErrorDeNegocio(
                "El estado 'prestado' solo se asigna al registrar un préstamo.",
                ErrorDeNegocio::CONFLICTO
            );
        }

        $trabajo = function () use ($idObjeto, $nuevoEstado): array {
            $objeto = $this->obtenerBloqueado($idObjeto);
            if ($objeto['estado'] === EstadoObjeto::PRESTADO) {
                throw new ErrorDeNegocio(
                    "El equipo \"{$objeto['nombre']}\" está prestado. Para liberarlo hay que registrar la devolución.",
                    ErrorDeNegocio::CONFLICTO
                );
            }
            if (!EstadoObjeto::puedeTransicionar($objeto['estado'], $nuevoEstado)) {
                throw new ErrorDeNegocio(
                    "No se puede pasar el equipo de '{$objeto['estado']}' a '{$nuevoEstado}'.",
                    ErrorDeNegocio::CONFLICTO
                );
            }
            $this->objetos->actualizarEstado($idObjeto, $nuevoEstado);
            return ['id_objeto' => $idObjeto, 'estado_anterior' => $objeto['estado'], 'estado' => $nuevoEstado];
        };

        return $this->transaccion ? $this->transaccion->ejecutar($trabajo) : $trabajo();
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
