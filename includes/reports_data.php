<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/movement_dates.php';

function reports_filters(array $input): array
{
    $section = (string) ($input['seccion'] ?? 'inventario');
    if (!in_array($section, ['inventario', 'movimientos'], true)) {
        $section = 'inventario';
    }
    $filters = ['seccion' => $section, 'estado' => '', 'categoria' => 0, 'ubicacion' => 0,
        'tipo' => '', 'activo' => 0, 'usuario' => 0, 'desde' => '', 'hasta' => ''];
    $states = ['disponible', 'asignado', 'mantenimiento', 'baja'];
    $types = ['alta', 'asignacion', 'transferencia', 'cambio_estado', 'mantenimiento', 'baja', 'edicion'];
    $state = (string) ($input['estado'] ?? '');
    $type = (string) ($input['tipo'] ?? '');
    $filters['estado'] = in_array($state, $states, true) ? $state : '';
    $filters['tipo'] = in_array($type, $types, true) ? $type : '';
    foreach (['categoria', 'ubicacion', 'activo', 'usuario'] as $key) {
        $filters[$key] = filter_var($input[$key] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: 0;
    }
    foreach (['desde', 'hasta'] as $key) {
        $date = trim((string) ($input[$key] ?? ''));
        if ($date !== '') {
            $parsed = DateTimeImmutable::createFromFormat('!Y-m-d', $date, new DateTimeZone('America/Mexico_City'));
            if (!$parsed || $parsed->format('Y-m-d') !== $date) {
                throw new InvalidArgumentException('La fecha ' . $key . ' no es válida.');
            }
        }
        $filters[$key] = $date;
    }
    if ($filters['desde'] !== '' && $filters['hasta'] !== '' && $filters['desde'] > $filters['hasta']) {
        throw new InvalidArgumentException('La fecha inicial no puede ser posterior a la fecha final.');
    }
    return $filters;
}

function reports_catalogs(): array
{
    $db = database();
    return [
        'categorias' => $db->query('SELECT id_categoria, nombre_categoria FROM categorias ORDER BY nombre_categoria')->fetchAll(),
        'ubicaciones' => $db->query('SELECT id_ubicacion, nombre_ubicacion FROM ubicaciones ORDER BY nombre_ubicacion')->fetchAll(),
        'activos' => $db->query('SELECT id_activo, nombre_activo, numero_serie FROM activos ORDER BY nombre_activo')->fetchAll(),
        'usuarios' => $db->query('SELECT id_usuario, nombre FROM usuarios ORDER BY nombre')->fetchAll(),
    ];
}

function reports_query(array $f): array
{
    $params = [];
    $conditions = [];
    if ($f['seccion'] === 'inventario') {
        $from = ' FROM activos a LEFT JOIN categorias c ON c.id_categoria = a.id_categoria LEFT JOIN ubicaciones u ON u.id_ubicacion = a.id_ubicacion LEFT JOIN usuarios r ON r.id_usuario = a.responsable';
        if ($f['estado'] !== '') { $conditions[] = 'a.estado = :estado'; $params['estado'] = $f['estado']; }
        if ($f['categoria'] > 0) { $conditions[] = 'a.id_categoria = :categoria'; $params['categoria'] = $f['categoria']; }
        if ($f['ubicacion'] > 0) { $conditions[] = 'a.id_ubicacion = :ubicacion'; $params['ubicacion'] = $f['ubicacion']; }
        $select = 'SELECT a.id_activo, a.nombre_activo, a.numero_serie, a.estado, c.nombre_categoria, u.nombre_ubicacion, r.nombre AS responsable';
        $order = ' ORDER BY a.id_activo DESC';
    } else {
        $from = ' FROM movimientos m INNER JOIN activos a ON a.id_activo = m.id_activo INNER JOIN usuarios u ON u.id_usuario = m.id_usuario';
        if ($f['tipo'] !== '') { $conditions[] = 'm.tipo_movimiento = :tipo'; $params['tipo'] = $f['tipo']; }
        if ($f['activo'] > 0) { $conditions[] = 'm.id_activo = :activo'; $params['activo'] = $f['activo']; }
        if ($f['usuario'] > 0) { $conditions[] = 'm.id_usuario = :usuario'; $params['usuario'] = $f['usuario']; }
        if ($f['desde'] !== '') {
            $conditions[] = 'm.fecha_movimiento >= :desde';
            $params['desde'] = movement_local_day_start_utc($f['desde']);
        }
        if ($f['hasta'] !== '') {
            $conditions[] = 'm.fecha_movimiento < :hasta';
            $nextDay = (new DateTimeImmutable($f['hasta'], new DateTimeZone('America/Mexico_City')))->modify('+1 day')->format('Y-m-d');
            $params['hasta'] = movement_local_day_start_utc($nextDay);
        }
        $select = 'SELECT m.id_movimiento, m.fecha_movimiento, m.tipo_movimiento, m.observaciones, a.nombre_activo, a.numero_serie, u.nombre AS registrado_por';
        $order = ' ORDER BY m.fecha_movimiento DESC, m.id_movimiento DESC';
    }
    return [$select, $from . ($conditions ? ' WHERE ' . implode(' AND ', $conditions) : ''), $order, $params];
}

function reports_execute(PDO $db, string $sql, array $params): PDOStatement
{
    $stmt = $db->prepare($sql);
    foreach ($params as $key => $value) {
        $stmt->bindValue(':' . $key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
    }
    $stmt->execute();
    return $stmt;
}

function reports_summary(): array
{
    $db = database();
    $summary = ['total' => (int) $db->query('SELECT COUNT(*) FROM activos')->fetchColumn(),
        'movimientos' => (int) $db->query('SELECT COUNT(*) FROM movimientos')->fetchColumn(),
        'estados' => [], 'categorias' => [], 'ubicaciones' => []];
    $summary['estados'] = $db->query('SELECT estado AS nombre, COUNT(*) AS cantidad FROM activos GROUP BY estado ORDER BY cantidad DESC')->fetchAll();
    $summary['categorias'] = $db->query('SELECT COALESCE(c.nombre_categoria, "Sin categoría") AS nombre, COUNT(*) AS cantidad FROM activos a LEFT JOIN categorias c ON c.id_categoria = a.id_categoria GROUP BY c.id_categoria, c.nombre_categoria ORDER BY cantidad DESC LIMIT 10')->fetchAll();
    $summary['ubicaciones'] = $db->query('SELECT COALESCE(u.nombre_ubicacion, "Sin ubicación") AS nombre, COUNT(*) AS cantidad FROM activos a LEFT JOIN ubicaciones u ON u.id_ubicacion = a.id_ubicacion GROUP BY u.id_ubicacion, u.nombre_ubicacion ORDER BY cantidad DESC LIMIT 10')->fetchAll();
    return $summary;
}

/** Impide que Excel interprete datos de inventario como fórmulas. */
function reports_csv_safe($value): string
{
    $text = (string) ($value ?? '');
    if (preg_match('/^[\s\x00-\x1F]*[=+@\-]/u', $text)) {
        return "'" . $text;
    }
    return $text;
}
