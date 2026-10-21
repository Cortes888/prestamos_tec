<?php
/**
 * POST src/devolver.php
 * Body: { id_prestamo, obs }
 *
 * Antes: CALL sp_registrar_devolucion, que respondía 'OK' aunque no
 *        modificara ninguna fila (préstamo inexistente o ya devuelto).
 * Ahora: PrestamoServicio::devolver() verifica el préstamo y el cambio (ADR-004).
 */
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';

use PrestamosTec\Compartido\Contenedor;
use PrestamosTec\Compartido\Http\RespuestaJson;

RespuestaJson::manejar(function (): array {
    $cuerpo = RespuestaJson::cuerpo();
    $idPrestamo = (int) ($cuerpo['id_prestamo'] ?? 0);
    $obs = trim((string) ($cuerpo['obs'] ?? '')) ?: 'Sin novedades';

    Contenedor::prestamos()->devolver($idPrestamo, $obs);
    return ['mensaje' => "OK: Devolución del préstamo #{$idPrestamo} registrada."];
});
