<?php
declare(strict_types=1);

namespace PrestamosTec\Compartido\Http;

use PrestamosTec\Compartido\ErrorDeNegocio;
use Throwable;

/**
 * Utilidades de la capa de presentación (controladores = archivos *.php de src/).
 *
 * Contrato con el frontend (index.html):
 *   - éxito  → HTTP 200 con el JSON de datos (o { mensaje, ... })
 *   - fallo  → HTTP 4xx/5xx con { error: "..." }, que el frontend muestra como error.
 */
final class RespuestaJson
{
    public static function cuerpo(): array
    {
        $datos = json_decode((string) file_get_contents('php://input'), true);
        return is_array($datos) ? $datos : [];
    }

    /** Ejecuta el controlador y traduce sus excepciones a respuestas JSON. */
    public static function manejar(callable $controlador): void
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $resultado = $controlador();
            echo json_encode($resultado, JSON_UNESCAPED_UNICODE);
        } catch (ErrorDeNegocio $e) {
            http_response_code($e->codigoHttp());
            echo json_encode(['error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
        } catch (Throwable $e) {
            error_log('[prestamos_tec] ' . $e);
            http_response_code(500);
            echo json_encode(['error' => 'Error interno del servidor.'], JSON_UNESCAPED_UNICODE);
        }
    }
}
