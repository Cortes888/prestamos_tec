<?php
/**
 * Punto de entrada de la API REST del MVP (ADR-005).
 *
 * El archivo .htaccess de la raíz envía aquí todo lo que empiece por /api/.
 * Aquí solo se declaran las rutas; cada una llama a un controlador.
 */
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';

use PrestamosTec\Compartido\Http\Router;
use PrestamosTec\Inventario\Http\ObjetoControlador;
use PrestamosTec\Prestamos\Http\PrestamoControlador;

$router = new Router();

// Inventario (módulo MVP)
$router->agregar('GET', '/objetos', [ObjetoControlador::class, 'listar']);
$router->agregar('PATCH', '/objetos/{id}/estado', [ObjetoControlador::class, 'cambiarEstado']);

// Préstamos
$router->agregar('GET', '/prestamos', [PrestamoControlador::class, 'listar']);
$router->agregar('POST', '/prestamos', [PrestamoControlador::class, 'registrar'], 201);
$router->agregar('POST', '/prestamos/{id}/devolucion', [PrestamoControlador::class, 'devolver']);

$router->despachar($_SERVER['REQUEST_METHOD'] ?? 'GET', Router::rutaSolicitada());
