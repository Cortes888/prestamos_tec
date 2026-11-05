<?php
declare(strict_types=1);

namespace PrestamosTec\Compartido;

use RuntimeException;

// Error de una regla del negocio. Guarda el código HTTP que se va a responder
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
