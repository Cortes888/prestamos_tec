<?php
declare(strict_types=1);

namespace PrestamosTec\Compartido\Http;

use PrestamosTec\Compartido\ErrorDeNegocio;
use Throwable;

// Responde en JSON. Si hay error devuelve {error} con el código HTTP
final class RespuestaJson
{
    public static function cuerpo(): array
    {
        $datos = json_decode((string) file_get_contents('php://input'), true);
        return is_array($datos) ? $datos : [];
    }

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
