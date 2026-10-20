
<?php

$sourceFile = __DIR__ . '/../reportes_exportar.php';

if (!is_file($sourceFile)) {
    throw new RuntimeException(
        'No se encontró reportes_exportar.php.'
    );
}

$source = file_get_contents($sourceFile);

if ($source === false) {
    throw new RuntimeException(
        'No fue posible leer reportes_exportar.php.'
    );
}

// Identificar el bloque de encabezados.
$start = strpos(
    $source,
    "if (\$section === 'inventario')"
);

$end = strpos(
    $source,
    'while ($row =',
    $start === false ? 0 : $start
);

if ($start === false || $end === false || $end <= $start) {
    throw new RuntimeException(
        'No se encontró el bloque de encabezados CSV.'
    );
}

$fragment = substr(
    $source,
    $start,
    $end - $start
);

// Crear una función auxiliar que ejecuta el fragmento original.
eval(
    'function reports_headers_for_test(string $section): array {
        $out = fopen("php://temp", "w+");

        if ($out === false) {
            throw new RuntimeException(
                "No fue posible crear el flujo temporal."
            );
        }

        ' . $fragment . '

        rewind($out);
        $headers = fgetcsv($out);
        fclose($out);

        if (!is_array($headers)) {
            throw new RuntimeException(
                "No se pudieron leer los encabezados CSV."
            );
        }

        return $headers;
    }'
);
