<?php
declare(strict_types=1);

namespace PrestamosTec\Inventario\Servicio;

use PrestamosTec\Compartido\ErrorDeNegocio;
use PrestamosTec\Compartido\Transaccion;
use PrestamosTec\Inventario\Dominio\EstadoObjeto;
use PrestamosTec\Inventario\Repositorio\ObjetoRepositorio;

// Es el único que cambia el estado de un equipo (ADR-002)
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

    // Se usa dentro de la transacción del préstamo
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

    // Si el equipo está prestado lo deja disponible
    public function liberarTrasDevolucion(int $idObjeto): void
    {
        $objeto = $this->obtenerBloqueado($idObjeto);
        if ($objeto['estado'] === EstadoObjeto::PRESTADO) {
            $this->objetos->actualizarEstado($idObjeto, EstadoObjeto::DISPONIBLE);
        }
    }

    // Cambio manual a mantenimiento, baja o disponible.
    // 'prestado' solo cambia al prestar o devolver
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
