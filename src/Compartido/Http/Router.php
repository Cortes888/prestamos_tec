<?php
declare(strict_types=1);

namespace PrestamosTec\Compartido\Http;

use PrestamosTec\Compartido\ErrorDeNegocio;

/**
 * Router de la API REST (patrón controlador frontal, ADR-005).
 *
 * Todas las peticiones a /api/... llegan a src/api.php. El router busca la
 * ruta que coincide con el método HTTP y la URL, y llama al controlador.
 * Las partes {id} de la ruta se pasan al controlador como números enteros.
 *
 *   404 si la ruta no existe
 *   405 si la ruta existe pero no acepta ese método
 */
final class Router
{
    /** @var array<int, array{metodo:string, patron:string, regex:string, accion:callable, codigo:int}> */
    private array $rutas = [];

    /**
     * @param callable $accion       Recibe los {id} de la ruta y devuelve los datos de la respuesta.
     * @param int      $codigoExito  Código HTTP cuando todo sale bien (200, o 201 al crear).
     */
    public function agregar(string $metodo, string $patron, callable $accion, int $codigoExito = 200): void
    {
        $regex = '#^' . preg_replace('#\{[a-z_]+\}#', '(\d+)', $patron) . '$#';
        $this->rutas[] = [
            'metodo' => strtoupper($metodo),
            'patron' => $patron,
            'regex' => $regex,
            'accion' => $accion,
            'codigo' => $codigoExito,
        ];
    }

    public function despachar(string $metodo, string $ruta): void
    {
        $metodo = strtoupper($metodo);
        $ruta = '/' . trim($ruta, '/');
        $permitidos = [];

        foreach ($this->rutas as $r) {
            if (!preg_match($r['regex'], $ruta, $partes)) {
                continue;
            }
            if ($r['metodo'] !== $metodo) {
                $permitidos[] = $r['metodo'];
                continue;
            }
            $parametros = array_map('intval', array_slice($partes, 1));
            RespuestaJson::manejar(fn () => ($r['accion'])(...$parametros), $r['codigo']);
            return;
        }

        RespuestaJson::manejar(function () use ($metodo, $ruta, $permitidos) {
            if ($permitidos) {
                header('Allow: ' . implode(', ', array_unique($permitidos)));
                throw new ErrorDeNegocio(
                    "La ruta /api{$ruta} no acepta el método {$metodo}.",
                    ErrorDeNegocio::METODO_NO_PERMITIDO
                );
            }
            throw new ErrorDeNegocio("Ruta no encontrada: {$metodo} /api{$ruta}", ErrorDeNegocio::NO_ENCONTRADO);
        });
    }

    /**
     * Saca la parte de la URL que va después de /api.
     * Funciona con la URL limpia (/prestamos/api/objetos, gracias al .htaccess)
     * y también sin .htaccess (/prestamos/src/api.php/objetos).
     */
    public static function rutaSolicitada(): string
    {
        if (!empty($_SERVER['PATH_INFO'])) {
            return (string) $_SERVER['PATH_INFO'];
        }
        $ruta = (string) parse_url((string) ($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH);
        $ruta = rawurldecode($ruta);
        foreach (['/api.php', '/api'] as $marca) {
            $pos = strpos($ruta, $marca . '/');
            if ($pos !== false) {
                return substr($ruta, $pos + strlen($marca));
            }
            if (substr($ruta, -strlen($marca)) === $marca) {
                return '/';
            }
        }
        return $ruta;
    }
}
