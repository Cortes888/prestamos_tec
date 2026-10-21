<?php
/**
 * GET src/objetos_disponibles.php — Equipos que se pueden prestar (ADR-001).
 */
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';

use PrestamosTec\Compartido\Contenedor;
use PrestamosTec\Compartido\Http\RespuestaJson;

RespuestaJson::manejar(fn () => Contenedor::inventario()->listarDisponibles());
