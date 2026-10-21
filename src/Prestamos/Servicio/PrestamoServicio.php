<?php
declare(strict_types=1);

namespace PrestamosTec\Prestamos\Servicio;

use PrestamosTec\Compartido\ErrorDeNegocio;
use PrestamosTec\Compartido\Transaccion;
use PrestamosTec\Inventario\Servicio\InventarioServicio;
use PrestamosTec\Prestamos\Repositorio\PrestamoRepositorio;
use RuntimeException;

/**
 * Casos de uso de préstamo y devolución.
 *
 * Reemplaza a sp_registrar_prestamo y sp_registrar_devolucion:
 *  - ADR-003: el préstamo se registra en una sola transacción con la fila del
 *    equipo bloqueada, de modo que dos solicitudes simultáneas no pueden
 *    aprobarse ambas.
 *  - ADR-004: la devolución verifica que el préstamo exista y siga abierto
 *    antes de responder; ya no hay "OK" falsos.
 *  - ADR-002: el estado del equipo lo cambia InventarioServicio, no un trigger.
 */
final class PrestamoServicio
{
    public const DIAS_MIN = 1;
    /** Mismo límite que ya tenía el formulario de index.html (input max="30"). */
    public const DIAS_MAX = 30;

    private PrestamoRepositorio $prestamos;
    private InventarioServicio $inventario;
    private Transaccion $transaccion;

    public function __construct(
        PrestamoRepositorio $prestamos,
        InventarioServicio $inventario,
        Transaccion $transaccion
    ) {
        $this->prestamos = $prestamos;
        $this->inventario = $inventario;
        $this->transaccion = $transaccion;
    }

    public function listar(): array
    {
        return $this->prestamos->listarConDetalle();
    }

    /** @return int id del préstamo creado */
    public function registrar(int $idUsuario, int $idObjeto, int $dias, string $observaciones = ''): int
    {
        if ($idUsuario <= 0 || $idObjeto <= 0) {
            throw new ErrorDeNegocio('Faltan datos: usuario y equipo son obligatorios.', ErrorDeNegocio::DATOS_INVALIDOS);
        }
        if ($dias < self::DIAS_MIN || $dias > self::DIAS_MAX) {
            throw new ErrorDeNegocio(
                'Los días de préstamo deben estar entre ' . self::DIAS_MIN . ' y ' . self::DIAS_MAX . '.',
                ErrorDeNegocio::DATOS_INVALIDOS
            );
        }
        if (!$this->prestamos->usuarioActivoExiste($idUsuario)) {
            throw new ErrorDeNegocio("El usuario #{$idUsuario} no existe o está inactivo.", ErrorDeNegocio::NO_ENCONTRADO);
        }

        return $this->transaccion->ejecutar(function () use ($idUsuario, $idObjeto, $dias, $observaciones): int {
            // 1. Bloquea la fila del equipo y valida que esté disponible.
            //    Una segunda solicitud simultánea queda esperando aquí.
            $this->inventario->marcarComoPrestado($idObjeto);
            // 2. Crea el préstamo. Si algo falla, la transacción deshace también el paso 1.
            return $this->prestamos->crear($idUsuario, $idObjeto, $dias, $observaciones);
        });
    }

    public function devolver(int $idPrestamo, string $observaciones = 'Sin novedades'): void
    {
        if ($idPrestamo <= 0) {
            throw new ErrorDeNegocio('Falta el número de préstamo.', ErrorDeNegocio::DATOS_INVALIDOS);
        }

        $this->transaccion->ejecutar(function () use ($idPrestamo, $observaciones): void {
            $prestamo = $this->prestamos->buscarYBloquear($idPrestamo);
            if ($prestamo === null) {
                throw new ErrorDeNegocio("El préstamo #{$idPrestamo} no existe.", ErrorDeNegocio::NO_ENCONTRADO);
            }
            if ($prestamo['estado'] === 'devuelto') {
                throw new ErrorDeNegocio("El préstamo #{$idPrestamo} ya había sido devuelto.", ErrorDeNegocio::CONFLICTO);
            }

            $filas = $this->prestamos->marcarDevuelto($idPrestamo, $observaciones);
            if ($filas !== 1) {
                // Con la fila bloqueada esto no debería pasar; si pasa, se deshace todo.
                throw new RuntimeException("La devolución del préstamo #{$idPrestamo} no modificó el registro esperado.");
            }

            $this->inventario->liberarTrasDevolucion($prestamo['id_objeto']);
        });
    }
}
