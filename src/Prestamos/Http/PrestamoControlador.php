<?php
declare(strict_types=1);

namespace PrestamosTec\Prestamos\Http;

use PrestamosTec\Compartido\Contenedor;
use PrestamosTec\Compartido\Http\RespuestaJson;

// GET y POST /api/prestamos y POST /api/prestamos/{id}/devolucion
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
