<?php
// Rutas de la API REST. El .htaccess manda aquí todo lo que empiece por /api/
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';

use PrestamosTec\Compartido\Http\Router;
use PrestamosTec\Inventario\Http\ObjetoControlador;
use PrestamosTec\Prestamos\Http\PrestamoControlador;

$router = new Router();

// Inventario
$router->agregar('GET', '/objetos', [ObjetoControlador::class, 'listar']);
$router->agregar('PATCH', '/objetos/{id}/estado', [ObjetoControlador::class, 'cambiarEstado']);

// Préstamos
$router->agregar('GET', '/prestamos', [PrestamoControlador::class, 'listar']);
$router->agregar('POST', '/prestamos', [PrestamoControlador::class, 'registrar'], 201);
$router->agregar('POST', '/prestamos/{id}/devolucion', [PrestamoControlador::class, 'devolver']);

$router->despachar($_SERVER['REQUEST_METHOD'] ?? 'GET', Router::rutaSolicitada());
