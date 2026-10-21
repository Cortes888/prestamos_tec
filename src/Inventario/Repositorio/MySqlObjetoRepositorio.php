<?php
declare(strict_types=1);

namespace PrestamosTec\Inventario\Repositorio;

use PDO;
use PrestamosTec\Inventario\Dominio\EstadoObjeto;

final class MySqlObjetoRepositorio implements ObjetoRepositorio
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function listarInventario(): array
    {
        $sql = "SELECT o.id_objeto, o.nombre, o.codigo_inv, o.estado, o.descripcion,
                       c.nombre AS categoria,
                       (SELECT COUNT(*) FROM prestamo p WHERE p.id_objeto = o.id_objeto) AS total_prestamos
                  FROM objeto o
                  JOIN categoria c ON c.id_categoria = o.id_categoria
                 WHERE o.estado <> :baja
                 ORDER BY o.id_objeto";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['baja' => EstadoObjeto::BAJA]);
        return $stmt->fetchAll();
    }

    public function listarDisponibles(): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT id_objeto, nombre, codigo_inv FROM objeto WHERE estado = :estado ORDER BY nombre"
        );
        $stmt->execute(['estado' => EstadoObjeto::DISPONIBLE]);
        return $stmt->fetchAll();
    }

    public function buscarYBloquear(int $idObjeto): ?array
    {
        $stmt = $this->pdo->prepare(
            "SELECT id_objeto, nombre, estado FROM objeto WHERE id_objeto = :id FOR UPDATE"
        );
        $stmt->execute(['id' => $idObjeto]);
        $fila = $stmt->fetch();
        return $fila === false ? null : $fila;
    }

    public function actualizarEstado(int $idObjeto, string $estado): void
    {
        $stmt = $this->pdo->prepare("UPDATE objeto SET estado = :estado WHERE id_objeto = :id");
        $stmt->execute(['estado' => $estado, 'id' => $idObjeto]);
    }
}
