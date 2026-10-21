<?php
/**
 * GET src/prestamos.php — Controlador (capa de presentación).
 * Antes: SQL + JOIN de 3 tablas + JSON en el mismo archivo.
 * Ahora: delega en PrestamoServicio → MySqlPrestamoRepositorio (ADR-001).
 */
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';

use PrestamosTec\Compartido\Contenedor;
use PrestamosTec\Compartido\Http\RespuestaJson;

RespuestaJson::manejar(fn () => Contenedor::prestamos()->listar());
