<?php
declare(strict_types=1);

namespace PrestamosTec\Compartido;

use RuntimeException;

/**
 * Error esperado de una regla de negocio (objeto no disponible, préstamo
 * inexistente, etc.). Lleva el código HTTP con el que se debe responder,
 * para que los controladores no tengan que decidirlo.
 */
class ErrorDeNegocio extends RuntimeException
{
    public const NO_ENCONTRADO = 404;
    public const METODO_NO_PERMITIDO = 405;
    public const CONFLICTO = 409;
    public const DATOS_INVALIDOS = 422;

    private int $codigoHttp;

    public function __construct(string $mensaje, int $codigoHttp = self::CONFLICTO)
    {
        parent::__construct($mensaje);
        $this->codigoHttp = $codigoHttp;
    }

    public function codigoHttp(): int
    {
        return $this->codigoHttp;
    }
}
