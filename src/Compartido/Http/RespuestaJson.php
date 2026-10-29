<?php
declare(strict_types=1);

namespace PrestamosTec\Compartido\Http;

use PrestamosTec\Compartido\ErrorDeNegocio;
use Throwable;

/**
 * Respuestas JSON de la API (capa de controladores).
 *
 * Contrato con el frontend (index.html):
 *   - éxito: HTTP 200 (o 201 al crear) con el JSON de datos o { mensaje, ... }
 *   - fallo: HTTP 4xx/5xx con { error: "..." }, que el frontend muestra como error.
 */
final class RespuestaJson
{
    public static function cuerpo(): array
    {
        $datos = json_decode((string) file_get_contents('php://input'), true);
        return is_array($datos) ? $datos : [];
    }

    /** Ejecuta el controlador y traduce sus excepciones a respuestas JSON. */
    public static function manejar(callable $controlador, int $codigoExito = 200): void
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $resultado = $controlador();
            http_response_code($codigoExito);
            echo json_encode($resultado, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        } catch (ErrorDeNegocio $e) {
            http_response_code($e->codigoHttp());
            echo json_encode(['error' => $e->getMessage()], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        } catch (Throwable $e) {
            error_log('[prestamos_tec] ' . $e);
            http_response_code(500);
            echo json_encode(['error' => 'Error interno del servidor.'], JSON_UNESCAPED_UNICODE);
        }
    }
}
