<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/reports_data.php';
require_login();
try {
    $filters = reports_filters($_GET);
} catch (InvalidArgumentException $exception) {
    http_response_code(400);
    exit('Filtros de reporte no válidos.');
}
[$select, $from, $order, $params] = reports_query($filters);
$db = database();
$stmt = reports_execute($db, $select . $from . $order, $params);
$section = $filters['seccion'];
header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="derevo_' . $section . '_' . date('Ymd') . '.csv"');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('X-Content-Type-Options: nosniff');
$out = fopen('php://output', 'wb');
fwrite($out, "\xEF\xBB\xBF");
if ($section === 'inventario') {
    fputcsv($out, ['ID', 'Activo', 'Número de serie', 'Estado', 'Categoría', 'Ubicación', 'Responsable']);
} else {
    fputcsv($out, ['ID', 'Fecha local (México)', 'Activo', 'Número de serie', 'Tipo', 'Registró', 'Observaciones']);
}
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    if ($section === 'inventario') {
        $values = [$row['id_activo'], $row['nombre_activo'], $row['numero_serie'], $row['estado'], $row['nombre_categoria'], $row['nombre_ubicacion'], $row['responsable']];
    } else {
        $values = [$row['id_movimiento'], movement_local_date($row['fecha_movimiento']), $row['nombre_activo'], $row['numero_serie'], $row['tipo_movimiento'], $row['registrado_por'], $row['observaciones']];
    }
    fputcsv($out, array_map('reports_csv_safe', $values));
}
fclose($out);
exit;
