<?php
declare(strict_types=1);

namespace PrestamosTec\Prestamos\Http;

use PrestamosTec\Compartido\Contenedor;
use PrestamosTec\Compartido\Http\RespuestaJson;

/**
 * Controlador REST de préstamos.
 *
 *   GET  /api/prestamos                   lista los préstamos
 *   POST /api/prestamos                   registra un préstamo (ADR-003)
 *   POST /api/prestamos/{id}/devolucion   registra la devolución (ADR-004)
 *
 * No tiene SQL ni reglas: lee la petición y llama a PrestamoServicio.
 * Los errores (404, 409, 422) los lanza el servicio y los convierte RespuestaJson.
 */
final class PrestamoControlador
{
    public static function listar(): array
    {
        return Contenedor::prestamos()->listar();
    }

    public static function registrar(): array
    {
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
    }

    public static function devolver(int $idPrestamo): array
    {
        $cuerpo = RespuestaJson::cuerpo();
        $obs = trim((string) ($cuerpo['obs'] ?? '')) ?: 'Sin novedades';

        Contenedor::prestamos()->devolver($idPrestamo, $obs);
        return [
            'mensaje' => "OK: Devolución del préstamo #{$idPrestamo} registrada.",
            'id_prestamo' => $idPrestamo,
        ];
    }
}
