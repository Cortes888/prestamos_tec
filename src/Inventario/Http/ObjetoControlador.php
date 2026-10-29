<?php
declare(strict_types=1);

namespace PrestamosTec\Inventario\Http;

use PrestamosTec\Compartido\Contenedor;
use PrestamosTec\Compartido\ErrorDeNegocio;
use PrestamosTec\Compartido\Http\RespuestaJson;

/**
 * Controlador REST del inventario (módulo MVP).
 *
 *   GET   /api/objetos                    lista el inventario
 *   GET   /api/objetos?estado=disponible  solo los equipos que se pueden prestar
 *   PATCH /api/objetos/{id}/estado        cambia a mantenimiento, baja o disponible
 *
 * No tiene SQL ni reglas: lee la petición y llama a InventarioServicio.
 */
final class ObjetoControlador
{
    public static function listar(): array
    {
        $estado = $_GET['estado'] ?? null;
        if ($estado === null || $estado === '') {
            return Contenedor::inventario()->listarInventario();
        }
        if ($estado === 'disponible') {
            return Contenedor::inventario()->listarDisponibles();
        }
        throw new ErrorDeNegocio(
            "Filtro no soportado: estado={$estado}. Use estado=disponible o quite el filtro.",
            ErrorDeNegocio::DATOS_INVALIDOS
        );
    }

    public static function cambiarEstado(int $idObjeto): array
    {
        $cuerpo = RespuestaJson::cuerpo();
        $estado = trim((string) ($cuerpo['estado'] ?? ''));
        if ($estado === '') {
            throw new ErrorDeNegocio('Falta el campo "estado" en el cuerpo de la petición.', ErrorDeNegocio::DATOS_INVALIDOS);
        }

        $resultado = Contenedor::inventario()->cambiarEstado($idObjeto, $estado);
        return ['mensaje' => "OK: Equipo #{$idObjeto} ahora está en estado {$estado}."] + $resultado;
    }
}
