<?php
/**
 * GET src/objetos.php — Controlador del inventario (ADR-001).
 * La consulta vive en MySqlObjetoRepositorio::listarInventario().
 */
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';

use PrestamosTec\Compartido\Contenedor;
use PrestamosTec\Compartido\Http\RespuestaJson;

RespuestaJson::manejar(fn () => Contenedor::inventario()->listarInventario());
