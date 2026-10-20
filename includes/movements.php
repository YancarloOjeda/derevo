<?php

declare(strict_types=1);

require_once __DIR__ . '/inventory.php';

/**
 * Guarda una modificación y su movimiento dentro de la misma transacción.
 * Los valores anteriores se obtienen con bloqueo de fila para evitar carreras.
 */
function movement_update_asset(
    PDO $db,
    int $assetId,
    array $changes,
    int $actorId,
    string $type,
    string $notes
): bool {
    $allowedTypes = ['asignacion', 'transferencia', 'cambio_estado', 'mantenimiento', 'baja', 'edicion'];
    if (!in_array($type, $allowedTypes, true)) {
        throw new InvalidArgumentException('Tipo de movimiento inválido.');
    }

    $db->beginTransaction();
    try {
        $stmt = $db->prepare('SELECT * FROM activos WHERE id_activo = :id FOR UPDATE');
        $stmt->execute(['id' => $assetId]);
        $old = $stmt->fetch();

        if (!$old) {
            throw new RuntimeException('El activo ya no existe.');
        }

        $new = array_replace($old, $changes);
        $tracked = ['id_ubicacion', 'responsable', 'estado'];
        $changed = false;
        foreach ($tracked as $field) {
            if ((string) ($old[$field] ?? '') !== (string) ($new[$field] ?? '')) {
                $changed = true;
            }
        }

        $update = $db->prepare('UPDATE activos SET
            nombre_activo = :nombre_activo, numero_serie = :numero_serie,
            descripcion = :descripcion, id_categoria = :id_categoria,
            id_ubicacion = :id_ubicacion, estado = :estado, responsable = :responsable
            WHERE id_activo = :id');
        $update->execute([
            'nombre_activo' => $new['nombre_activo'],
            'numero_serie' => $new['numero_serie'],
            'descripcion' => $new['descripcion'],
            'id_categoria' => $new['id_categoria'],
            'id_ubicacion' => $new['id_ubicacion'],
            'estado' => $new['estado'],
            'responsable' => $new['responsable'],
            'id' => $assetId,
        ]);

        if ($changed) {
            $insert = $db->prepare('INSERT INTO movimientos
                (id_activo, id_usuario, tipo_movimiento, observaciones,
                 ubicacion_anterior, ubicacion_nueva, responsable_anterior,
                 responsable_nuevo, estado_anterior, estado_nuevo)
                VALUES (:id_activo, :id_usuario, :tipo, :observaciones,
                        :ubicacion_anterior, :ubicacion_nueva, :responsable_anterior,
                        :responsable_nuevo, :estado_anterior, :estado_nuevo)');
            $insert->execute([
                'id_activo' => $assetId,
                'id_usuario' => $actorId,
                'tipo' => $type,
                'observaciones' => $notes !== '' ? $notes : null,
                'ubicacion_anterior' => $old['id_ubicacion'],
                'ubicacion_nueva' => $new['id_ubicacion'],
                'responsable_anterior' => $old['responsable'],
                'responsable_nuevo' => $new['responsable'],
                'estado_anterior' => $old['estado'],
                'estado_nuevo' => $new['estado'],
            ]);
        }
        $db->commit();
        return $changed;
    } catch (Throwable $error) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        throw $error;
    }
}

/** Identifica el tipo de cambio a partir de los valores enviados. */
function movement_type(array $old, array $new): string
{
    if ($old['estado'] !== $new['estado']) {
        if ($new['estado'] === 'baja') {
            return 'baja';
        }
        if ($new['estado'] === 'mantenimiento') {
            return 'mantenimiento';
        }
        return 'cambio_estado';
    }
    if ((int) ($old['responsable'] ?? 0) !== (int) ($new['responsable'] ?? 0)) {
        return empty($old['responsable']) ? 'asignacion' : 'transferencia';
    }
    return 'transferencia';
}
