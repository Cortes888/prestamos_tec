<?php
declare(strict_types=1);

namespace PrestamosTec\Inventario\Dominio;

/**
 * Fuente única de verdad sobre los estados de un equipo (ADR-002).
 *
 * Antes, la regla "qué estado tiene un equipo y cuándo cambia" estaba repartida
 * entre el ENUM de la tabla objeto, la comparación con 'disponible' dentro de
 * sp_registrar_prestamo y los triggers de la tabla prestamo. Ahora vive aquí.
 *
 * Para agregar un estado nuevo (p. ej. 'reservado'): añadir la constante,
 * sus transiciones en TRANSICIONES y el valor al ENUM de la tabla objeto.
 * No hay que tocar el módulo de Préstamos.
 */
final class EstadoObjeto
{
    public const DISPONIBLE = 'disponible';
    public const PRESTADO = 'prestado';
    public const MANTENIMIENTO = 'mantenimiento';
    public const BAJA = 'baja';

    /** Estado actual => estados a los que puede pasar. */
    private const TRANSICIONES = [
        self::DISPONIBLE => [self::PRESTADO, self::MANTENIMIENTO, self::BAJA],
        self::PRESTADO => [self::DISPONIBLE],
        self::MANTENIMIENTO => [self::DISPONIBLE, self::BAJA],
        self::BAJA => [],
    ];

    /** @return string[] */
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
