<?php
declare(strict_types=1);

namespace PrestamosTec\Compartido;

// Ejecuta varias operaciones juntas: se guarda todo o nada
interface Transaccion
{
    public function ejecutar(callable $trabajo);
}
