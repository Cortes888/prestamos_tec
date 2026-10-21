<?php
declare(strict_types=1);

namespace PrestamosTec\Compartido;

/**
 * Ejecuta un trabajo como una unidad atómica: o se guarda todo o no se
 * guarda nada (ADR-003). Los servicios dependen de esta interfaz y no de
 * PDO, lo que permite probarlos sin base de datos.
 */
interface Transaccion
{
    /**
     * @template T
     * @param callable():T $trabajo
     * @return T
     */
    public function ejecutar(callable $trabajo);
}
