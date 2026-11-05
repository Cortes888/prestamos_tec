<?php
declare(strict_types=1);

namespace PrestamosTec\Inventario\Dominio;

// Estados de un equipo y a cuáles puede pasar desde cada uno (ADR-002)
final class EstadoObjeto
{
    public const DISPONIBLE = 'disponible';
    public const PRESTADO = 'prestado';
    public const MANTENIMIENTO = 'mantenimiento';
    public const BAJA = 'baja';

    private const TRANSICIONES = [
        self::DISPONIBLE => [self::PRESTADO, self::MANTENIMIENTO, self::BAJA],
        self::PRESTADO => [self::DISPONIBLE],
        self::MANTENIMIENTO => [self::DISPONIBLE, self::BAJA],
        self::BAJA => [],
    ];

    public static function todos(): array
    {
        return array_keys(self::TRANSICIONES);
    }

    public static function esValido(string $estado): bool
    {
        return array_key_exists($estado, self::TRANSICIONES);
    }

    public static function puedeTransicionar(string $desde, string $hacia): bool
    {
        return self::esValido($desde) && in_array($hacia, self::TRANSICIONES[$desde], true);
    }

    public static function permitePrestamo(string $estado): bool
    {
        return self::puedeTransicionar($estado, self::PRESTADO);
    }
}
