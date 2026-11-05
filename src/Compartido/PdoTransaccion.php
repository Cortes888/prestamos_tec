<?php
declare(strict_types=1);

namespace PrestamosTec\Compartido;

use PDO;
use PDOException;
use Throwable;

final class PdoTransaccion implements Transaccion
{
// Errores de MySQL cuando dos operaciones chocan. Se responden como 409
    private const ERRORES_DE_CONCURRENCIA = [
        1213, // deadlock
        1205, // tiempo de espera del bloqueo
        1062, // clave duplicada
    ];

    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function ejecutar(callable $trabajo)
    {
        $this->pdo->beginTransaction();
        try {
            $resultado = $trabajo();
            $this->pdo->commit();
            return $resultado;
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw self::traducir($e);
        }
    }

    public static function traducir(Throwable $e): Throwable
    {
        if ($e instanceof PDOException
            && in_array((int) ($e->errorInfo[1] ?? 0), self::ERRORES_DE_CONCURRENCIA, true)) {
            return new ErrorDeNegocio(
                'Otra operación modificó este equipo al mismo tiempo. Actualice la lista e intente de nuevo.',
                ErrorDeNegocio::CONFLICTO
            );
        }
        return $e;
    }
}
