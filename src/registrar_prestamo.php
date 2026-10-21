<?php
/**
 * POST src/registrar_prestamo.php
 * Body: { id_usuario, id_objeto, dias, obs }
 *
 * Antes: CALL sp_registrar_prestamo (consulta y luego inserta, sin bloqueo)
 *        y respondía { mensaje: "ERROR: ..." }, que el frontend mostraba como éxito.
 * Ahora: PrestamoServicio::registrar() en una transacción con la fila del equipo
 *        bloqueada (ADR-003). Los rechazos llegan como { error } con HTTP 4xx.
 */
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';

use PrestamosTec\Compartido\Contenedor;
use PrestamosTec\Compartido\Http\RespuestaJson;

RespuestaJson::manejar(function (): array {
    $cuerpo = RespuestaJson::cuerpo();
    $idPrestamo = Contenedor::prestamos()->registrar(
        (int) ($cuerpo['id_usuario'] ?? 0),
        (int) ($cuerpo['id_objeto'] ?? 0),
        (int) ($cuerpo['dias'] ?? 3),
        trim((string) ($cuerpo['obs'] ?? ''))
    );
    return [
        'mensaje' => "OK: Préstamo #{$idPrestamo} registrado.",
        'id_prestamo' => $idPrestamo,
    ];
});
