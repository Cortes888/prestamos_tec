<?php
declare(strict_types=1);

namespace PrestamosTec\Prestamos\Repositorio;

use PDO;
use PDOException;
use PrestamosTec\Compartido\ErrorDeNegocio;

final class MySqlPrestamoRepositorio implements PrestamoRepositorio
{
    /** Código MySQL de clave duplicada (índice único violado). */
    private const ER_DUP_ENTRY = 1062;

    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function listarConDetalle(): array
    {
        $sql = "SELECT p.id_prestamo,
                       CONCAT(u.nombre, ' ', u.apellido) AS usuario,
                       o.nombre AS objeto,
                       p.fecha_prestamo, p.fecha_devolucion_esperada,
                       p.fecha_devolucion_real, p.estado
                  FROM prestamo p
                  JOIN usuario u ON u.id_usuario = p.id_usuario
                  JOIN objeto  o ON o.id_objeto  = p.id_objeto
                 ORDER BY p.id_prestamo DESC";
        return $this->pdo->query($sql)->fetchAll();
    }

    public function usuarioActivoExiste(int $idUsuario): bool
    {
        $stmt = $this->pdo->prepare("SELECT 1 FROM usuario WHERE id_usuario = :id AND activo = 1");
        $stmt->execute(['id' => $idUsuario]);
        return $stmt->fetchColumn() !== false;
    }

    public function crear(int $idUsuario, int $idObjeto, int $dias, string $observaciones): int
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO prestamo (id_usuario, id_objeto, fecha_devolucion_esperada, observaciones)
             VALUES (:usuario, :objeto, NOW() + INTERVAL :dias DAY, :obs)"
        );
        try {
            $stmt->execute([
                'usuario' => $idUsuario,
                'objeto' => $idObjeto,
                'dias' => $dias,
                'obs' => $observaciones,
            ]);
        } catch (PDOException $e) {
            if ((int) ($e->errorInfo[1] ?? 0) === self::ER_DUP_ENTRY) {
                throw new ErrorDeNegocio('El equipo ya tiene un préstamo activo.', ErrorDeNegocio::CONFLICTO);
            }
            throw $e;
        }
        return (int) $this->pdo->lastInsertId();
    }

    public function buscarYBloquear(int $idPrestamo): ?array
    {
        $stmt = $this->pdo->prepare(
            "SELECT id_prestamo, id_objeto, estado FROM prestamo WHERE id_prestamo = :id FOR UPDATE"
        );
        $stmt->execute(['id' => $idPrestamo]);
        $fila = $stmt->fetch();
        if ($fila === false) {
            return null;
        }
        $fila['id_prestamo'] = (int) $fila['id_prestamo'];
        $fila['id_objeto'] = (int) $fila['id_objeto'];
        return $fila;
    }

    public function marcarDevuelto(int $idPrestamo, string $observaciones): int
    {
        $stmt = $this->pdo->prepare(
            "UPDATE prestamo
                SET estado = 'devuelto',
                    fecha_devolucion_real = NOW(),
                    observaciones = CONCAT(IFNULL(observaciones, ''), ' | Devolución: ', :obs)
              WHERE id_prestamo = :id AND estado <> 'devuelto'"
        );
        $stmt->execute(['obs' => $observaciones, 'id' => $idPrestamo]);
        return $stmt->rowCount();
    }
}
