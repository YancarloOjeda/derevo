
<?php

$sourceFile = __DIR__ . '/../includes/movements.php';

if (!is_file($sourceFile)) {
    throw new RuntimeException(
        'No se encontró movements.php: ' . $sourceFile
    );
}

$source = file_get_contents($sourceFile);

if ($source === false) {
    throw new RuntimeException(
        'No fue posible leer movements.php.'
    );
}

// Localizar el bloque original de inserción.
$start = strpos($source, 'if ($changed) {');

if ($start === false) {
    throw new RuntimeException(
        'No se encontró la condición $changed.'
    );
}

// Localizar el cierre del bloque antes del commit.
$end = strpos($source, '$db->commit();', $start);

if ($end === false) {
    throw new RuntimeException(
        'No se encontró el final del bloque.'
    );
}

$fragment = substr(
    $source,
    $start,
    $end - $start
);

// Ejecutar el fragmento original con datos controlados.
eval(
    'function movement_insert_for_test(
        PDO $db,
        bool $changed,
        string $notes
    ): void {

        $assetId = 10;
        $actorId = 2;
        $type = "edicion";

        $old = [
            "id_ubicacion" => 1,
            "responsable" => 3,
            "estado" => "disponible"
        ];

        $new = [
            "id_ubicacion" => 2,
            "responsable" => 3,
            "estado" => "disponible"
        ];

        ' . $fragment . '
    }'
);
