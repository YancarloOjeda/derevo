
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

// Extraer el fragmento original de validación.
$start = strpos($source, '$allowedTypes =');

$endMarker = '$db->beginTransaction();';
$end = strpos($source, $endMarker, $start ?: 0);

if ($start === false || $end === false || $end <= $start) {
    throw new RuntimeException(
        'No se encontró el fragmento de validación.'
    );
}

$validationCode = substr(
    $source,
    $start,
    $end - $start
);

// Crear una función de prueba que ejecuta el fragmento original.
eval(
    'function movement_validate_type_for_test(string $type): bool {' .
    $validationCode .
    'return true;' .
    '}'
);
