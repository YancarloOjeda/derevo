
<?php

// Cargar únicamente movement_type() para pruebas unitarias.

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

$start = strpos($source, 'function movement_type(');

if ($start === false) {
    throw new RuntimeException(
        'No se encontró la función movement_type().'
    );
}

// movement_type() es la última función del archivo original.
$functionCode = substr($source, $start);

eval($functionCode);
