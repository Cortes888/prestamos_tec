<?php
declare(strict_types=1);

namespace PrestamosTec\Compartido;

use PDO;
use PDOException;
use Throwable;

final class PdoTransaccion implements Transaccion
{
    /**
     * Códigos de MySQL con los que la base aborta una transacción por
     * chocar con otra simultánea. No son fallos del sistema: se informan como
     * conflicto (HTTP 409) para que el usuario pueda reintentar.
     */
    private const ERRORES_DE_CONCURRENCIA = [
        1213, // ER_LOCK_DEADLOCK
        1205, // ER_LOCK_WAIT_TIMEOUT
        1062, // ER_DUP_ENTRY (p. ej. índice uq_prestamo_abierto_por_objeto al confirmar)
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

    /** Convierte los errores de concurrencia de la base en ErrorDeNegocio 409. */
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
